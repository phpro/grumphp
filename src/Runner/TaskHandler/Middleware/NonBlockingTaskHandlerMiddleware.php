<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use GrumPHP\Runner\StopOnFailure;
use function Amp\async;
use Amp\Future;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class NonBlockingTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    public function handle(
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        StopOnFailure $stopOnFailure,
        callable $next
    ): Future {
        return async(
            static function () use ($task, $runnerContext, $next, $stopOnFailure): TaskResultInterface {
                $result = $next($task, $runnerContext, $stopOnFailure)->await();

                if ($result->isPassed() || $result->isSkipped() || $task->getConfig()->getMetadata()->isBlocking()) {
                    return $result;
                }

                return TaskResult::createNonBlockingFailed(
                    $result->getTask(),
                    $result->getContext(),
                    $result->getMessage()
                );
            }
        );
    }
}
