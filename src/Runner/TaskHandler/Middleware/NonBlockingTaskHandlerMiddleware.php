<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class NonBlockingTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    public function handle(
        TaskInterface $task,
        ContextInterface $context,
        callable $next
    ): TaskResultInterface {
        /** @var TaskResultInterface $result */
        $result = $next($task, $context);
        if ($result->isPassed() || $result->isSkipped()) {
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
