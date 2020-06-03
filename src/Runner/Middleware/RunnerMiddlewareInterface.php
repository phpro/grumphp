<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskRunnerContext;

interface RunnerMiddlewareInterface
{
    /**
     * @param callable(TaskRunnerContext $info): TaskResultCollection $next
     */
    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection;
}
