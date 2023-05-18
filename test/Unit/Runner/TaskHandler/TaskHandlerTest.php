<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\TaskHandler;

use Amp\Future;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Runner\StopOnFailure;
use GrumPHP\Runner\TaskHandler\Middleware\TaskHandlerMiddlewareInterface;
use GrumPHP\Runner\TaskHandler\TaskHandler;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class TaskHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function it_has_a_default_fallback_middleware(): void
    {
        $stack = new TaskHandler();
        $result = $stack->handle($task = $this->createTask(), $context = $this->createContext(), StopOnFailure::dummy())->await();

        self::assertEquals(
            TaskResult::createFailed($task, $context->getTaskContext(), 'Task could not be handled by a task handler!'),
            $result
        );
    }

    /** @test */
    public function it_can_run_middlewares_in_a_stack(): void
    {
        $task = $this->createTask();
        $context = $this->createContext();
        $expectedResult = TaskResult::createPassed($task, $context->getTaskContext());

        $stack = new TaskHandler(
            $this->createMiddleware(function (TaskInterface $task, TaskRunnerContext $context, StopOnFailure $stopOnFailure, callable $next): Future {
                return $next($task, $context, $stopOnFailure);
            }),
            $this->createMiddleware(function (TaskInterface $task, TaskRunnerContext $context, StopOnFailure $stopOnFailure, callable $next) use (
                $expectedResult
            ): Future {
                return Future::complete($expectedResult);
            }),
            $this->createMiddleware(function (TaskInterface $task, TaskRunnerContext $context, StopOnFailure $stopOnFailure, callable $next): Future {
                return $next($task, $context, $stopOnFailure);
            })
        );

        $result = $stack->handle($task, $context, StopOnFailure::dummy())->await();


        self::assertSame($expectedResult, $result);
    }


    private function createMiddleware(callable $run): TaskHandlerMiddlewareInterface
    {
        /** @var ObjectProphecy|TaskHandlerMiddlewareInterface $middleware */
        $middleware = $this->prophesize(TaskHandlerMiddlewareInterface::class);
        $middleware->handle(Argument::cetera())->will(function ($arguments) use ($run): Future {
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

    private function createTask(): TaskInterface
    {
        return $this->prophesize(TaskInterface::class)->reveal();
    }
}
