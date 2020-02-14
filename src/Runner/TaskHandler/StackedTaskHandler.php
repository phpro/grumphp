<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler;

use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class StackedTaskHandler implements TaskHandlerInterface
{
    /**
     * @var TaskHandlerInterface[]
     */
    private $stack;

    public function __construct(TaskHandlerInterface ... $stack)
    {
        $this->stack = $stack;
    }

    public function handle(TaskInterface $task, ContextInterface $context, callable $next): TaskResultInterface
    {
        // TODO : $next is not being used here ... How to 'skip it' ?

        $this->createStack()($task, $context);
    }

    private function createStack(): callable
    {
        $lastCallable = function () {};
        $handlerList = $this->stack;

        while($handler = array_pop($handlerList)) {
            $lastCallable = static function (TaskInterface $task, ContextInterface $context) use ($handler, $lastCallable) : TaskResultInterface {
                return $handler->handle($task, $context, $lastCallable);
            };
        }

        return $lastCallable;
    }
}
