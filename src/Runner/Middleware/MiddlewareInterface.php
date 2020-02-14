<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\RunnerInfo;
use GrumPHP\Runner\Stack\StackInterface;

interface MiddlewareInterface
{
    /**
     * @psalm-param callable(RunnerInfo $info): TaskResultCollection $next
     */
    public function handle(RunnerInfo $info, callable $next): TaskResultCollection;
}
