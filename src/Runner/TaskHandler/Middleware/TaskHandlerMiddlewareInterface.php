<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use Amp\Future;
use GrumPHP\Runner\StopOnFailure;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

interface TaskHandlerMiddlewareInterface
{
    /**
     * @param callable(TaskInterface, TaskRunnerContext, StopOnFailure): Future<TaskResultInterface> $next
     * @return Future<TaskResultInterface>
     */
    public function handle(
        TaskInterface $task,
        TaskRunnerContext $runnerContext,
        StopOnFailure $stopOnFailure,
        callable $next
    ): Future;
}
