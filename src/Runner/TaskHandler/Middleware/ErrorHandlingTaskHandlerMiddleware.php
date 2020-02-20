<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use function Amp\call;
use Amp\Promise;
use GrumPHP\Exception\PlatformException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class ErrorHandlingTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    public function handle(
        TaskInterface $task,
        ContextInterface $context,
        callable $next
    ): Promise {
        return call(
            static function () use ($task, $context): TaskResultInterface {
                try {
                    $result = $task->run($context);
                } catch (PlatformException $e) {
                    return TaskResult::createSkipped($task, $context);
                } catch (RuntimeException $e) {
                    return TaskResult::createFailed($task, $context, $e->getMessage());
                }

                return $result;
            }
        );
    }
}
