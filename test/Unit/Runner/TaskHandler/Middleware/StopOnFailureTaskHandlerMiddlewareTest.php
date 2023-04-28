<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\TaskHandler\Middleware;

use GrumPHP\Configuration\Model\RunnerConfig;
use GrumPHP\Runner\StopOnFailure;
use GrumPHP\Runner\TaskHandler\Middleware\StopOnFailureTaskHandlerMiddleware;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Test\Runner\AbstractTaskHandlerMiddlewareTestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class StopOnFailureTaskHandlerMiddlewareTest extends AbstractTaskHandlerMiddlewareTestCase
{
    use ProphecyTrait;

    private StopOnFailureTaskHandlerMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new StopOnFailureTaskHandlerMiddleware();
    }

    /** @test */
    public function it_decides_if_a_result_should_stop_task_execition(): void
    {
        $stopOnFailure = StopOnFailure::createFromConfig(
            new RunnerConfig(stopOnFailure: true, hideCircumventionTip: true, additionalInfo: null)
        );
        $cancellation = $stopOnFailure->cancellation();

        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $taskContext = $context->getTaskContext();
        $expectedResult = TaskResult::createFailed($task, $taskContext, 'message');
        $next = $this->createNextResultCallback($expectedResult);

        $promise = $this->middleware->handle($task, $context, $stopOnFailure, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($expectedResult, $actualResult);
        self::assertTrue($cancellation->isRequested());
    }
}
