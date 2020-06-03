<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use function Amp\call;
use Amp\Promise;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class NonBlockingTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    public function handle(
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        callable $next
    ): Promise {
        return call(
            /**
             * @return \Generator<mixed, Promise<TaskResultInterface>, mixed, TaskResultInterface>
             */
            static function () use ($task, $runnerContext, $next): \Generator {
                /** @var TaskResultInterface $result */
                $result = yield $next($task, $runnerContext);

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
