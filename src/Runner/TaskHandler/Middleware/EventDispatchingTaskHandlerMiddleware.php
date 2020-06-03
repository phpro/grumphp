<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use function Amp\call;
use Amp\Promise;
use GrumPHP\Event\Dispatcher\EventDispatcherInterface;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Event\TaskFailedEvent;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class EventDispatchingTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(TaskInterface $task, TaskRunnerContext $runnerContext, callable $next): Promise
    {
        return call(
            /**
             * @return \Generator<mixed, Promise<TaskResultInterface>, mixed, TaskResultInterface>
             */
            function () use ($task, $runnerContext, $next): \Generator {
                $taskContext = $runnerContext->getTaskContext();
                $this->eventDispatcher->dispatch(new TaskEvent($task, $taskContext), TaskEvents::TASK_RUN);

                /** @var TaskResultInterface $result */
                $result = yield $next($task, $runnerContext);

                if ($result->isSkipped()) {
                    $this->eventDispatcher->dispatch(new TaskEvent($task, $taskContext), TaskEvents::TASK_SKIPPED);
                    return $result;
                }

                if ($result->hasFailed()) {
                    $e = new RuntimeException($result->getMessage());
                    $this->eventDispatcher->dispatch(
                        new TaskFailedEvent($task, $taskContext, $e),
                        TaskEvents::TASK_FAILED
                    );

                    return $result;
                }

                $this->eventDispatcher->dispatch(new TaskEvent($task, $taskContext), TaskEvents::TASK_COMPLETE);

                return $result;
            }
        );
    }
}
