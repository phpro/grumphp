<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskRunnerContext;

class StopOnFailureRunnerMiddleware implements RunnerMiddlewareInterface
{
    public function handle(TaskRunnerContext $context, callable $next): TaskResultCollection
    {
        // TODO

        // $this->grumPHP->stopOnFailure()

        return $next($context);
    }
}
