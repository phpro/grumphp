<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\RunnerInfo;
use GrumPHP\Runner\Stack\StackInterface;

interface MiddlewareInterface
{
    /**
     * @return TaskResultCollection
     */
    public function handle(RunnerInfo $info, StackInterface $stack): TaskResultCollection;
}
