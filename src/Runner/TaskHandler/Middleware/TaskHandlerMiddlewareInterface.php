<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use Amp\Promise;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\TaskInterface;

interface TaskHandlerMiddlewareInterface
{
    /**
     * @param callable(TaskInterface, TaskRunnerContext): Promise<TaskResultInterface> $next
     * @return Promise<TaskResultInterface>
     */
    public function handle(
        TaskInterface $task,
        TaskRunnerContext $runnercontext,
        callable $next
    ): Promise;
}
