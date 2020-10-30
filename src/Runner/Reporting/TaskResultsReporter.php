<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Reporting;

use GrumPHP\Event\TaskEvents;
use GrumPHP\IO\IOInterface;
use GrumPHP\Runner\Ci\CiDetector;
use GrumPHP\Runner\MemoizedTaskResultMap;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class TaskResultsReporter
{
    /**
     * @var IOInterface
     */
    private $IO;

    /**
     * @var ConsoleSectionOutput|null
     */
    private $outputSection;

    /**
     * @var MemoizedTaskResultMap
     */
    private $taskResultMap;

    /**
     * @var CiDetector
     */
    private $ciDetector;

    public function __construct(IOInterface $IO, MemoizedTaskResultMap $taskResultMap, CiDetector $ciDetector)
    {
        $this->IO = $IO;
        $this->taskResultMap = $taskResultMap;
        $this->ciDetector = $ciDetector;
    }

    /**
     * This method can be used to report output of tasks within a specific scope.
     * It can be used to create new sections e.g. per grouped tasks by priority.
     * If skip_on_failure is triggered, the output section will be closed when
     * the amp/parallel promises return an error (because of canceled promise)
     *
     * @template TValue
     * @param callable(): TValue $actionsInSection
     * @return TValue
     */
    public function runInSection(callable $actionsInSection)
    {
        $this->outputSection = $this->IO->section();
        $result = $actionsInSection();
        $this->outputSection = null;

        return $result;
    }

    public function report(TaskRunnerContext $context): void
    {
        if (!$this->outputSection || !$this->shouldRenderReport($context)) {
            return;
        }

        $info = 'Running task %s/%s: %s... %s';
        $tasks = $this->parseTasksDisplayMap($context);

        $message = [];
        $i=1;
        $total = count($tasks);
        foreach ($tasks as $name => $label) {
            $message[] = sprintf($info, $i, $total, $label, $this->displayTaskResult($name));
            $i++;
        }

        // Always add content if we decided that an overwrite is not possible!
        if (!$this->isOverwritePossible()) {
            $this->outputSection->writeln(array_merge($message, ['']));
            return;
        }

        $this->outputSection->overwrite(implode(PHP_EOL, $message));
    }

    private function parseTasksDisplayMap(TaskRunnerContext $context): array
    {
        return array_reduce(
            $context->getTasks()->toArray(),
            static function (array $taskMap, TaskInterface $task) : array {
                $config = $task->getConfig();
                return array_merge(
                    $taskMap,
                    [
                        $config->getName() => $config->getMetadata()->label()?: $config->getName()
                    ]
                );
            },
            []
        );
    }

    private function displayTaskResult(string $taskName): string
    {
        if (!$result = $this->taskResultMap->get($taskName)) {
            return '';
        }

        switch ($result->getResultCode()) {
            case TaskResultInterface::PASSED:
                return '<fg=green>✔</fg=green>';
            case TaskResultInterface::NONBLOCKING_FAILED:
                return '<fg=yellow>✘</fg=yellow>';
            case TaskResultInterface::FAILED:
                return '<fg=red>✘</fg=red>';
            case TaskEvents::TASK_SKIPPED:
                return implode(PHP_EOL, [
                    '<fg=yellow>Oh no, we hit the windows cmd input limit!</fg=yellow>',
                    '<fg=yellow>Skipping task...</fg=yellow>'
                ]);
        }

        return '';
    }

    /**
     * When the input is decorated (ansi), we can always overwrite the rendered content.
     * Otherwise (no-ansi), we only render on start and when all task results are reported.
     */
    private function shouldRenderReport(TaskRunnerContext $context): bool
    {
        if ($this->isOverwritePossible()) {
            return true;
        }

        $reportedCount = array_reduce(
            $context->getTasks()->toArray(),
            function (int $count, TaskInterface $task): int {
                return $this->taskResultMap->contains($task->getConfig()->getName()) ? $count+1 : $count;
            },
            0
        );

        return $reportedCount === 0 || $reportedCount === count($context->getTasks());
    }

    private function isOverwritePossible(): bool
    {
        return $this->IO->isDecorated() && !$this->ciDetector->isCiDetected();
    }
}
