<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use GrumPHP\Runner\StopOnFailure;
use function Amp\async;
use Amp\Future;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

class StopOnFailureTaskHandlerMiddleware implements TaskHandlerMiddlewareInterface
{
    public function handle(
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        StopOnFailure $stopOnFailure,
        callable $next
    ): Future {
        return async(
            static function () use ($task, $runnerContext, $stopOnFailure, $next): TaskResultInterface {
                $result = $next($task, $runnerContext, $stopOnFailure)->await();

                $stopOnFailure->decideForResult($result);

                return $result;
            }
        );
    }
}
