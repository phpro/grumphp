<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

interface TaskHandlerInterface
{
    /**
     * @param callable<TaskResultInterface, TaskInterface, ContextInterface> $next
     */
    public function handle(
        TaskInterface $task,
        ContextInterface $context,
        callable $next
    ): TaskResultInterface;
}
