<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler;

use GrumPHP\Runner\TaskHandler\Middleware\TaskHandlerInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class TaskHandlerStack
{
    /**
     * @psalm-var callable(TaskInterface, ContextInterface): TaskResultInterface
     * @var callable
     */
    private $stack;

    public function __construct(TaskHandlerInterface ... $handlers)
    {
        $this->stack = $this->createStack($handlers);
    }

    /**
     * @psalm-pure
     */
    public function handle(TaskInterface $task, ContextInterface $context): TaskResultInterface
    {
        return ($this->stack)($task, $context);
    }

    /**
     * @psalm-param TaskHandlerInterface[] $handlers
     * @psalm-return callable(TaskInterface, ContextInterface): TaskResultInterface
     */
    private function createStack(array $handlers): callable
    {
        $lastCallable = $this->fail();

        while($handler = array_pop($handlers)) {
            $lastCallable = static function (TaskInterface $task, ContextInterface $context) use (
                $handler,
                $lastCallable
            ) : TaskResultInterface {
                return $handler->handle($task, $context, $lastCallable);
            };
        }

        return $lastCallable;
    }

    /**
     * @psalm-return callable(TaskInterface, ContextInterface): TaskResultInterface
     */
    private function fail(): callable
    {
        return static function (TaskInterface $task, ContextInterface $context): TaskResultInterface {
            return TaskResult::createFailed($task, $context, 'Task could not be handled by a task handler!');
        };
    }
}
