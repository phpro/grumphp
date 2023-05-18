<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use GrumPHP\Runner\StopOnFailure;
use function Amp\async;
use Amp\Future;
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

    public function handle(
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        StopOnFailure $stopOnFailure,
        callable $next
    ): Future {
        return async(
            function () use ($task, $runnerContext, $stopOnFailure, $next): TaskResultInterface {
                $taskContext = $runnerContext->getTaskContext();
                $this->eventDispatcher->dispatch(new TaskEvent($task, $taskContext), TaskEvents::TASK_RUN);

                $result = $next($task, $runnerContext, $stopOnFailure)->await();

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
