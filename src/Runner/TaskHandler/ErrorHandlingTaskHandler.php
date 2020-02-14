<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler;

use GrumPHP\Exception\PlatformException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class ErrorHandlingTaskHandler implements TaskHandlerInterface
{
    public function handle(
        TaskInterface $task,
        ContextInterface $context,
        callable $next
    ): TaskResultInterface {
        try {
            return $task->run($context);
        } catch (PlatformException $e) {
            return TaskResult::createSkipped($task, $context);
        } catch (RuntimeException $e) {
            return TaskResult::createFailed($task, $context, $e->getMessage());
        }
    }
}
