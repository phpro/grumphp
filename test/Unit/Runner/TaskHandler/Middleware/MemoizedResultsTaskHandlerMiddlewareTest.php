<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\TaskHandler\Middleware;

use GrumPHP\Runner\MemoizedTaskResultMap;
use GrumPHP\Runner\TaskHandler\Middleware\MemoizedResultsTaskHandlerMiddleware;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Test\Runner\AbstractTaskHandlerMiddlewareTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class MemoizedResultsTaskHandlerMiddlewareTest extends AbstractTaskHandlerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var MemoizedTaskResultMap
     */
    private $map;

    /**
     * @var MemoizedResultsTaskHandlerMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $this->map = new MemoizedTaskResultMap();
        $this->middleware = new MemoizedResultsTaskHandlerMiddleware(
            $this->map
        );
    }

    /** @test */
    public function it_can_handle_success(): void
    {
        $context = $this->createRunnerContext();
        $taskContext = $context->getTaskContext();
        $task = $this->mockTask('task');
        $next = $this->createNextResultCallback($expectedResult = TaskResult::createPassed($task, $taskContext));

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($expectedResult, $actualResult);
        self::assertSame($this->map->get('task'), $actualResult);
    }

    /** @test */
    public function it_can_handle_exceptions(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $next = $this->createExceptionCallback($excetion = new \Exception('error'));

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($task, $actualResult->getTask());
        self::assertSame($context->getTaskContext(), $actualResult->getContext());
        self::assertFalse($actualResult->isPassed());
        self::assertTrue($actualResult->isBlocking());
        self::assertSame('error', $actualResult->getMessage());
        self::assertSame($this->map->get('task'), $actualResult);
    }
}
