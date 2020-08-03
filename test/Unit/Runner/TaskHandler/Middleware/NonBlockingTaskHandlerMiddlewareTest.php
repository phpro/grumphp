<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\TaskHandler\Middleware;

use GrumPHP\Exception\PlatformException;
use GrumPHP\Runner\TaskHandler\Middleware\NonBlockingTaskHandlerMiddleware;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Test\Runner\AbstractTaskHandlerMiddlewareTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class NonBlockingTaskHandlerMiddlewareTest extends AbstractTaskHandlerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var NonBlockingTaskHandlerMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $this->middleware = new NonBlockingTaskHandlerMiddleware();
    }

    /** @test */
    public function it_does_nothing_on_success(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $taskContext = $context->getTaskContext();
        $expectedResult = TaskResult::createPassed($task, $taskContext);
        $next = $this->createNextResultCallback($expectedResult);

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($expectedResult, $actualResult);
    }

    /** @test */
    public function it_does_nothing_on_skipped(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $taskContext = $context->getTaskContext();
        $expectedResult = TaskResult::createSkipped($task, $taskContext);
        $next = $this->createNextResultCallback($expectedResult);

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($expectedResult, $actualResult);
    }

    /** @test */
    public function it_does_nothing_on_failed_for_a_blocking_task(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task', ['blocking' => true]);
        $taskContext = $context->getTaskContext();
        $expectedResult = TaskResult::createFailed($task, $taskContext, 'error');
        $next = $this->createNextResultCallback($expectedResult);

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($expectedResult, $actualResult);
    }

    /** @test */
    public function it_marks_result_as_non_blocking_on_a_failed_non_blocking_task(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task', ['blocking' => false]);
        $taskContext = $context->getTaskContext();
        $taskResult = TaskResult::createFailed($task, $taskContext, 'error');
        $next = $this->createNextResultCallback($taskResult);

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertNotSame($taskResult, $actualResult);
        self::assertSame($task, $actualResult->getTask());
        self::assertSame($context->getTaskContext(), $actualResult->getContext());
        self::assertFalse($actualResult->isPassed());
        self::assertFalse($actualResult->isBlocking());
        self::assertSame('error', $actualResult->getMessage());
    }
}
