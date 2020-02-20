<?php

declare(strict_types=1);

namespace GrumPHP\Runner\TaskHandler;

use Amp\Promise;
use Amp\Success;
use GrumPHP\Runner\TaskHandler\Middleware\TaskHandlerMiddlewareInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class TaskHandler
{
    /**
     * @psalm-var callable(TaskInterface, ContextInterface): Promise<TaskResultInterface>
     * @var callable
     */
    private $stack;

    public function __construct(TaskHandlerMiddlewareInterface ... $handlers)
    {
        $this->stack = $this->createStack($handlers);
    }

    /**
     * Shortcut function to work directly with tagged services from the Symfony service container.
     * @psalm-param iterable<TaskHandlerMiddlewareInterface> $handlers
     */
    public static function fromIterable(iterable $handlers): self
    {
        return new self(
            ...($handlers instanceof \Traversable ? iterator_to_array($handlers) : $handlers)
        );
    }

    /**
     * @psalm-pure
     * @psalm-return Promise<TaskResultInterface>
     */
    public function handle(TaskInterface $task, ContextInterface $context): Promise
    {
        return ($this->stack)($task, $context);
    }

    /**
     * @param TaskHandlerMiddlewareInterface[] $handlers
     * @psalm-return callable(TaskInterface, ContextInterface): Promise<TaskResultInterface>
     */
    private function createStack(array $handlers): callable
    {
        $lastCallable = $this->fail();

        while($handler = array_pop($handlers)) {
            $lastCallable = static function (TaskInterface $task, ContextInterface $context) use (
                $handler,
                $lastCallable
            ) : Promise {
                return $handler->handle($task, $context, $lastCallable);
            };
        }

        return $lastCallable;
    }

    /**
     * @psalm-return callable(TaskInterface, ContextInterface): Promise<TaskResultInterface>
     */
    private function fail(): callable
    {
        return static function (TaskInterface $task, ContextInterface $context): Promise {
            /** @psalm-var TaskResultInterface $result */
            $result = TaskResult::createFailed($task, $context, 'Task could not be handled by a task handler!');

            return new Success($result);
        };
    }
}
