<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Configuration\Model\RunnerConfig;
use function Amp\call;
use Amp\CancelledException;
use Amp\LazyPromise;
use Amp\MultiReasonException;
use function Amp\Promise\wait;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\Promise\MultiPromise;
use GrumPHP\Runner\TaskHandler\TaskHandler;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class HandleRunnerMiddleware implements RunnerMiddlewareInterface
{
    /**
     * @var TaskHandler
     */
    private $taskHandler;

    /**
     * @var RunnerConfig
     */
    private $config;

    public function __construct(TaskHandler $taskHandler, RunnerConfig $config)
    {
        $this->taskHandler = $taskHandler;
        $this->config = $config;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        return new TaskResultCollection(
            (array) wait(
                /**
                 * @return \Generator<mixed, mixed, mixed, TaskResultInterface[]>
                 */
                call(function () use ($context): \Generator {
                    /**
                     * @var \Throwable[] $errors
                     * @var TaskResultInterface[] $results
                     * @psalm-suppress InvalidArrayAccess
                     * @psalm-suppress InvalidArrayOffset
                     */
                    [$errors, $results] = yield MultiPromise::cancelable(
                        $this->handleTasks($context),
                        function (TaskResultInterface $result) {
                            return $this->config->stopOnFailure() && $result->hasFailed();
                        }
                    );

                    // Filter out canceled items:
                    $errors = array_filter($errors, function (\Throwable $error): bool {
                        return !$error instanceof CancelledException;
                    });

                    if ($errors) {
                        throw new MultiReasonException($errors);
                    }

                    return $results;
                })
            )
        );
    }

    /**
     * @return array<int, LazyPromise<TaskResultInterface>>
     */
    private function handleTasks(TaskRunnerContext $context): array
    {
        return array_map(
            /**
             * @return LazyPromise<TaskResultInterface>
             */
            function (TaskInterface $task) use ($context) : LazyPromise {
                return new LazyPromise(function () use ($task, $context) {
                    return $this->taskHandler->handle($task, $context);
                });
            },
            $context->getTasks()->toArray()
        );
    }
}
