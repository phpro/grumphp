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

final class TaskResultsReporter
{
    /**
     * @var IOInterface
     */
    private $IO;

    /**
     * @var ConsoleSectionOutput
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
        $this->useNewSection();
    }

    public function useNewSection(): void
    {
        $this->outputSection = $this->IO->section();
    }

    public function report(TaskRunnerContext $context): void
    {
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

    public function displayTaskResult(string $taskName): string
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
                // TODO : fix regular skipped once?
                return implode(PHP_EOL, [
                    '<fg=yellow>Oh no, we hit the windows cmd input limit!</fg=yellow>',
                    '<fg=yellow>Skipping task...</fg=yellow>'
                ]);
        }

        return '';
    }
}
