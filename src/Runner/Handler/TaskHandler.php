<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Handler;

use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Event\TaskFailedEvent;
use GrumPHP\Exception\PlatformException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class TaskHandler implements TaskHandlerInterface
{
    public function handle(TaskInterface $task, ContextInterface $context): TaskResultInterface
    {
        try {
            $result = $task->run($context);
        } catch (PlatformException $e) {
            return TaskResult::createSkipped($task, $context);
        } catch (RuntimeException $e) {
            return TaskResult::createFailed($task, $context, $e->getMessage());
        }

        if (!$result instanceof TaskResultInterface) {
            throw RuntimeException::invalidTaskReturnType($task);
        }

        return $result;






    }
}
