<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Handler;

use GrumPHP\Event\Dispatcher\EventDispatcherInterface;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Event\TaskFailedEvent;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class EventDispatchingTaskHandler implements TaskHandlerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var TaskHandlerInterface
     */
    private $taskHandler;

    public function __construct(EventDispatcherInterface $eventDispatcher, TaskHandlerInterface $taskHandler)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->taskHandler = $taskHandler;
    }

    public function handle(TaskInterface $task, ContextInterface $context): TaskResultInterface
    {
        $this->eventDispatcher->dispatch(new TaskEvent($task, $context), TaskEvents::TASK_RUN);
        $result = $this->taskHandler->handle($task, $context);

        if ($result->isSkipped()) {
            $this->eventDispatcher->dispatch(new TaskEvent($task, $context), TaskEvents::TASK_SKIPPED);
            return $result;
        }

        if ($result->hasFailed()) {
            $e = new RuntimeException($result->getMessage());
            $this->eventDispatcher->dispatch(new TaskFailedEvent($task, $context, $e), TaskEvents::TASK_FAILED);

            return $result;
        }

        $this->eventDispatcher->dispatch(new TaskEvent($task, $context), TaskEvents::TASK_COMPLETE);

        return $result;
    }
}
