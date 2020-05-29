<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Reporting;

use GrumPHP\Event\TaskEvents;
use GrumPHP\IO\IOInterface;
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

    public function __construct(IOInterface $IO, MemoizedTaskResultMap $taskResultMap)
    {
        $this->IO = $IO;
        $this->taskResultMap = $taskResultMap;
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
        // Only log when there is an output section available!
        if (!$this->outputSection) {
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
}
