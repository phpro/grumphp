<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\Middleware\RunnerMiddlewareInterface;

class MiddlewareStack
{
    /**
     * @var callable(TaskRunnerContext): TaskResultCollection
     */
    private $stack;

    public function __construct(RunnerMiddlewareInterface ...$middlewares)
    {
        $this->stack = $this->createStack($middlewares);
    }

    /**
     * Shortcut function to work directly with tagged services from the Symfony service container.
     * @param iterable<RunnerMiddlewareInterface> $middlewares
     */
    public static function fromIterable(iterable $middlewares): self
    {
        return new self(
            ...($middlewares instanceof \Traversable ? iterator_to_array($middlewares) : $middlewares)
        );
    }

    /**
     * @psalm-pure
     */
    public function handle(TaskRunnerContext $context): TaskResultCollection
    {
        return ($this->stack)($context);
    }

    /**
     * @param RunnerMiddlewareInterface[] $middlewares
     * @return callable(TaskRunnerContext): TaskResultCollection
     */
    private function createStack(array $middlewares): callable
    {
        $lastCallable = $this->fail();

        while ($middleware = array_pop($middlewares)) {
            $lastCallable = static function (TaskRunnerContext $context) use (
                $middleware,
                $lastCallable
            ) : TaskResultCollection {
                return $middleware->handle($context, $lastCallable);
            };
        }

        return $lastCallable;
    }

    /**
     * @return callable(TaskRunnerContext): TaskResultCollection
     */
    private function fail(): callable
    {
        return static function (): TaskResultCollection {
            return new TaskResultCollection();
        };
    }
}
