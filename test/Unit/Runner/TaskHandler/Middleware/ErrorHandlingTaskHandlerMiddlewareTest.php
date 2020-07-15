<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\TaskHandler\Middleware;

use GrumPHP\Exception\PlatformException;
use GrumPHP\Runner\TaskHandler\Middleware\ErrorHandlingTaskHandlerMiddleware;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Test\Runner\AbstractTaskHandlerMiddlewareTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ErrorHandlingTaskHandlerMiddlewareTest extends AbstractTaskHandlerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var ErrorHandlingTaskHandlerMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $this->middleware = new ErrorHandlingTaskHandlerMiddleware();
    }

    /** @test */
    public function it_can_handle_success(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTaskRun('task', function (array $arguments) {
            return TaskResult::createPassed($this->reveal(), $arguments[0]);
        });
        $next = $this->createNextShouldNotBeCalledCallback();

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($task, $actualResult->getTask());
        self::assertSame($context->getTaskContext(), $actualResult->getContext());
        self::assertTrue($actualResult->isPassed());
    }

    /** @test */
    public function it_can_handle_platform_exceptions(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTaskRun('task', function () {
            throw new PlatformException('some exception');
        });
        $next = $this->createNextShouldNotBeCalledCallback();

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($task, $actualResult->getTask());
        self::assertSame($context->getTaskContext(), $actualResult->getContext());
        self::assertTrue($actualResult->isSkipped());
        self::assertSame('', $actualResult->getMessage());
    }

    /** @test */
    public function it_can_handle_other_exceptions(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTaskRun('task', function () {
            throw new \RuntimeException('some exception');
        });
        $next = $this->createNextShouldNotBeCalledCallback();

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($task, $actualResult->getTask());
        self::assertSame($context->getTaskContext(), $actualResult->getContext());
        self::assertFalse($actualResult->isPassed());
        self::assertTrue($actualResult->isBlocking());
        self::assertSame('some exception', $actualResult->getMessage());
    }
}
