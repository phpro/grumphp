<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\Middleware\RunnerMiddlewareInterface;
use GrumPHP\Runner\MiddlewareStack;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\RunContext;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class MiddlewareStackTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_has_a_default_fallback_middleware(): void
    {
        $stack = new MiddlewareStack();
        $result = $stack->handle($this->createContext());

        self::assertEquals(new TaskResultCollection(), $result);
    }

    /** @test */
    public function it_can_run_middlewares_in_a_stack(): void
    {
        $expectedResult = new TaskResultCollection();
        $stack = new MiddlewareStack(
            $this->createMiddleware(function (TaskRunnerContext $context, callable $next): TaskResultCollection {
                return $next($context);
            }),
            $this->createMiddleware(function (TaskRunnerContext $context, callable $next) use (
                $expectedResult
            ): TaskResultCollection {
                return $expectedResult;
            }),
            $this->createMiddleware(function (TaskRunnerContext $context, callable $next): TaskResultCollection {
                return $next($context);
            })
        );
        $result = $stack->handle($this->createContext());

        self::assertSame($expectedResult, $result);
    }

    private function createMiddleware(callable $run): RunnerMiddlewareInterface
    {
        /** @var ObjectProphecy|RunnerMiddlewareInterface $middleware */
        $middleware = $this->prophesize(RunnerMiddlewareInterface::class);
        $middleware->handle(Argument::cetera())->will(function ($arguments) use ($run): TaskResultCollection {
            return $run(...$arguments);
        });

        return $middleware->reveal();
    }

    private function createContext(): TaskRunnerContext
    {
        return new TaskRunnerContext(
            new RunContext(new FilesCollection())
        );
    }
}
