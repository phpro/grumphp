<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use function Amp\call;
use Amp\Promise;
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
    ): Promise {
        return call(
            /**
             * @psalm-return \Generator<mixed, Promise<TaskResultInterface>, mixed, TaskResultInterface>
             */
            static function () use ($task, $context, $next): \Generator {
                /** @var TaskResultInterface $result */
                $result = yield $next($task, $context);

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
