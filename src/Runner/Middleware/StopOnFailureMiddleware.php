<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\RunnerInfo;

class StopOnFailureMiddleware implements MiddlewareInterface
{
    public function handle(RunnerInfo $info, callable $next): TaskResultCollection
    {
        // TODO

        // $this->grumPHP->stopOnFailure()

        return $next($info);
    }
}
