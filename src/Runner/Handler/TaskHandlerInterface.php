<?php

declare(strict_types=1);

namespace GrumPHP\Runner\Handler;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

interface TaskHandlerInterface
{
    public function handle(TaskInterface $task, ContextInterface $context): TaskResultInterface;
}
