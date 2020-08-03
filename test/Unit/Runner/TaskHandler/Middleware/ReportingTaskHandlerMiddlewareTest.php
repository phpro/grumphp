<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\TaskHandler\Middleware;

use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Event\TaskFailedEvent;
use GrumPHP\Runner\Reporting\TaskResultsReporter;
use GrumPHP\Runner\TaskHandler\Middleware\ReportingTaskHandlerMiddleware;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Test\Runner\AbstractTaskHandlerMiddlewareTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ReportingTaskHandlerMiddlewareTest extends AbstractTaskHandlerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|TaskResultsReporter
     */
    private $taskReporter;

    /**
     * @var ReportingTaskHandlerMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $this->taskReporter = $this->prophesize(TaskResultsReporter::class);
        $this->middleware = new ReportingTaskHandlerMiddleware(
            $this->taskReporter->reveal()
        );
    }

    /** @test */
    public function it_reports_results(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $taskContext = $context->getTaskContext();
        $expectedResult = TaskResult::createPassed($task, $taskContext);
        $next = $this->createNextResultCallback($expectedResult);

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($actualResult, $expectedResult);

        $this->taskReporter->report($context)->shouldBeCalled();
    }
}
