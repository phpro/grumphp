<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\TaskHandler\Middleware;

use GrumPHP\Configuration\Model\ParallelConfig;
use GrumPHP\Runner\Parallel\PoolFactory;
use GrumPHP\Runner\Reporting\TaskResultsReporter;
use GrumPHP\Runner\TaskHandler\Middleware\ParallelProcessingMiddleware;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Test\Runner\AbstractTaskHandlerMiddlewareTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ParallelProcessingMiddlewareTest extends AbstractTaskHandlerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|TaskResultsReporter
     */
    private $taskReporter;

    /**
     * @var ParallelProcessingMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $this->taskReporter = $this->prophesize(TaskResultsReporter::class);
        $this->middleware = new ParallelProcessingMiddleware(
            $config = new ParallelConfig($enabled = false, $maxSize = 10),
            new PoolFactory($config),
            $this->mockIO()
        );
    }

    /** @test */
    public function it_runs_in_serial_when_parallel_is_disabled(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $taskContext = $context->getTaskContext();
        $expectedResult = TaskResult::createPassed($task, $taskContext);
        $next = $this->createNextResultCallback($expectedResult);

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($actualResult, $expectedResult);
    }
}
