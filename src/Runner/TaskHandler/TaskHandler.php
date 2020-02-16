<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler;

use GrumPHP\Runner\TaskHandler\Middleware\TaskHandlerMiddlewareInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class TaskHandler
{
    /**
     * @psalm-var callable(TaskInterface, ContextInterface): TaskResultInterface
     * @var callable
     */
    private $stack;

    public function __construct(TaskHandlerMiddlewareInterface ... $handlers)
    {
        $this->stack = $this->createStack($handlers);
    }

    /**
     * Shortcut function to work directly with tagged services from the Symfony service container.
     */
    public static function fromIterable(iterable $handlers): self
    {
        return new self(
            ...($handlers instanceof \Traversable ? iterator_to_array($handlers) : $handlers)
        );
    }

    /**
     * @psalm-pure
     */
    public function handle(TaskInterface $task, ContextInterface $context): TaskResultInterface
    {
        return ($this->stack)($task, $context);
    }

    /**
     * @psalm-param TaskHandlerMiddlewareInterface[] $handlers
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
