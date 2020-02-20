<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use function Amp\call;
use Amp\MultiReasonException;
use Amp\Promise;
use function Amp\Promise\any;
use function Amp\Promise\wait;
use GrumPHP\Collection\TaskResultCollection;
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

    public function __construct(TaskHandler $taskHandler)
    {
        $this->taskHandler = $taskHandler;
    }

    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
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
                    [$errors, $results] = yield any(
                        array_map(
                            /**
                             * @psalm-return Promise<TaskResultInterface>
                             */
                            function (TaskInterface $task) use ($context): Promise  {
                                return $this->taskHandler->handle($task, $context->getTaskContext());
                            },
                            $context->getTasks()->toArray()
                        )
                    );

                    if ($errors) {
                        $exception = new MultiReasonException($errors);
                        var_dump($exception->getReasons());exit;

                    }

                    return $results;
                })
            )
        );
    }
}
