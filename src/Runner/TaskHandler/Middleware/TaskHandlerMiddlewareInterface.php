<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler\Middleware;

use Amp\Promise;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

interface TaskHandlerMiddlewareInterface
{
    /**
     * @psalm-param callable(TaskInterface, ContextInterface): Promise<TaskResultInterface> $next
     * @psalm-return Promise<TaskResultInterface>
     */
    public function handle(
        TaskInterface $task,
        ContextInterface $context,
        callable $next
    ): Promise;
}
