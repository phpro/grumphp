<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use function Amp\call;
use Amp\MultiReasonException;
use Amp\Promise;
use function Amp\Promise\any;
use function Amp\Promise\wait;
use function Amp\Promise\wrap;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Event\TaskEvents;
use GrumPHP\IO\IOInterface;
use GrumPHP\Runner\TaskHandler\TaskHandler;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

class HandleRunnerMiddleware implements RunnerMiddlewareInterface
{
    /**
     * @var IOInterface
     */
    private $IO;

    /**
     * @var TaskHandler
     */
    private $taskHandler;

    /**
     * @var ConsoleSectionOutput
     */
    private $outputSection;

    /**
     * @var array<string, TaskResultInterface>
     */
    private $resultMap = [];

    public function __construct(IOInterface $IO, TaskHandler $taskHandler)
    {
        $this->IO = $IO;
        $this->taskHandler = $taskHandler;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        $this->outputSection = $this->IO->section();
        $this->report($context);

        return new TaskResultCollection(
            (array) wait(
                /**
                 * @psalm-return \Generator<mixed, mixed, mixed, TaskResultInterface[]>
                 */
                call(function () use ($context): \Generator {
                    /**
                     * @var \Throwable[] $errors
                     * @var TaskResultInterface[] $results
                     * @psalm-suppress InvalidArrayOffset
                     */
                    [$errors, $results] = yield any($this->handleTasks($context));

                    if ($errors) {
                        $exception = new MultiReasonException($errors);
                        var_dump($exception->getReasons());exit;

                    }

                    return $results;
                })
            )
        );
    }

    private function handleTasks(TaskRunnerContext $context): array
    {
        return array_map(
            /**
             * @psalm-return Promise<TaskResultInterface>
             */
            function (TaskInterface $task) use ($context): Promise  {
                return wrap(
                    $this->taskHandler->handle($task, $context->getTaskContext()),
                    function (?\Throwable $error, ?TaskResultInterface $result) use ($context) {
                        if ($error) {
                            throw $error;
                        }

                        $this->resultMap[$result->getTask()->getConfig()->getName()] = $result;
                        $this->report($context);

                        return $result;
                    }
                );
            },
            $context->getTasks()->toArray()
        );
    }

    private function report(TaskRunnerContext $context): void
    {
        $info = 'Running task %s/%s: %s... %s';
        $tasks = array_reduce(
            $context->getTasks()->toArray(),
            static function(array $taskmap, TaskInterface $task): array{
                $config = $task->getConfig();
                return array_merge(
                    $taskmap,
                    [
                        $config->getName() => $config->getMetadata()->label()?: $config->getName()
                    ]
                );
            },
            []
        );

        $message = [];
        $i=1;
        $total = count($tasks);
        foreach ($tasks as $name => $label) {
            $result = $this->resultMap[$name] ?? null;
            $message[] = sprintf($info, $i, $total, $label, $result ? $this->styleResult($result) : '');
            $i++;
        }

        $this->outputSection->overwrite(implode(PHP_EOL, $message));
    }

    private function styleResult(TaskResultInterface $result): string
    {
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
