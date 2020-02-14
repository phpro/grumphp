<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\Middleware\MiddlewareInterface;

class MiddlewareStack
{
    /**
     * @psalm-var callable(RunnerInfo): TaskResultCollection
     * @var callable
     */
    private $stack;

    public function __construct(MiddlewareInterface ... $middlewares)
    {
        $this->stack = $this->createStack($middlewares);
    }

    /**
     * @psalm-pure
     */
    public function handle(RunnerInfo $info): TaskResultCollection
    {
        return ($this->stack)($info);
    }

    /**
     * @psalm-param MiddlewareInterface[] $middlewares
     * @psalm-return callable(RunnerInfo): TaskResultCollection
     */
    private function createStack(array $middlewares): callable
    {
        $lastCallable = $this->fail();

        while($middleware = array_pop($middlewares)) {
            $lastCallable = static function (RunnerInfo $info) use (
                $middleware,
                $lastCallable
            ) : TaskResultCollection {
                return $middleware->handle($info, $lastCallable);
            };
        }

        return $lastCallable;
    }

    /**
     * @psalm-return callable(RunnerInfo): TaskResultCollection
     */
    private function fail(): callable
    {
        return static function (): TaskResultCollection {
            return new TaskResultCollection();
        };
    }
}
