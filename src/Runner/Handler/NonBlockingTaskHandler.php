<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Handler;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class NonBlockingTaskHandler implements TaskHandlerInterface
{
    /**
     * @var TaskHandlerInterface
     */
    private $taskHandler;

    public function __construct(TaskHandlerInterface $taskHandler)
    {
        $this->taskHandler = $taskHandler;
    }

    public function handle(TaskInterface $task, ContextInterface $context): TaskResultInterface
    {
        $result = $this->taskHandler->handle($task, $context);
        if ($result->isPassed()) {
            return $result;
        }

        if ($task->getConfig()->getMetadata()->isBlocking()) {
            return $result;
        }

        return TaskResult::createNonBlockingFailed(
            $result->getTask(),
            $result->getContext(),
            $result->getMessage()
        );
    }
}
