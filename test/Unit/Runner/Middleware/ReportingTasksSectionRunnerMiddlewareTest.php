<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\Middleware\ReportingTasksSectionRunnerMiddleware;
use GrumPHP\Runner\Reporting\TaskResultsReporter;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Test\Runner\AbstractRunnerMiddlewareTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ReportingTasksSectionRunnerMiddlewareTest extends AbstractRunnerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var ReportingTasksSectionRunnerMiddleware
     */
    private $middleware;

    /**
     * @var ObjectProphecy|TaskResultsReporter
     */
    private $taskResultsReporter;

    protected function setUp(): void
    {
        $this->taskResultsReporter = $this->prophesize(TaskResultsReporter::class);
        $this->middleware = new ReportingTasksSectionRunnerMiddleware(
            $this->taskResultsReporter->reveal()
        );
    }

    /** @test */
    public function it_can_report_running_tasks(): void
    {
        $context = $this->createRunnerContext();
        $next = static function (TaskRunnerContext $passedContext) {
            return new TaskResultCollection();
        };

        $this->taskResultsReporter->report($context)->shouldBeCalled();
        $this->taskResultsReporter->runInSection(Argument::type('callable'))->will(
            function ($arguments) {
                return $arguments[0]();
            }
        );

        $result = $this->middleware->handle($context, $next);
        self::assertEquals(new TaskResultCollection(), $result);
    }
}
