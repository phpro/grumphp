<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use GrumPHP\Runner\StopOnFailure;
use function Amp\async;
use Amp\Future;
use GrumPHP\Exception\PlatformException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class ErrorHandlingTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    public function handle(
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        StopOnFailure $stopOnFailure,
        callable $next
    ): Future {
        return async(
            static function () use ($task, $runnerContext): TaskResultInterface {
                $taskContext = $runnerContext->getTaskContext();
                try {
                    $result = $task->run($taskContext);
                } catch (PlatformException $e) {
                    return TaskResult::createSkipped($task, $taskContext);
                } catch (\Throwable $e) {
                    return TaskResult::createFailed($task, $taskContext, $e->getMessage());
                }

                return $result;
            }
        );
    }
}
