<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\Middleware\ReportingRunnerMiddleware;
use GrumPHP\Runner\Reporting\RunnerReporter;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Test\Runner\AbstractRunnerMiddlewareTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ReportingRunnerMiddlewareTest extends AbstractRunnerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var ReportingRunnerMiddleware
     */
    private $middleware;

    /**
     * @var ObjectProphecy|RunnerReporter
     */
    private $runnerReporter;

    protected function setUp(): void
    {
        $this->runnerReporter = $this->prophesize(RunnerReporter::class);
        $this->middleware = new ReportingRunnerMiddleware(
            $this->runnerReporter->reveal()
        );
    }

    /** @test */
    public function it_can_report_runner_results(): void
    {
        $context = $this->createRunnerContext();
        $next = static function (TaskRunnerContext $passedContext) {
            return new TaskResultCollection();
        };

        $result = $this->middleware->handle($context, $next);
        self::assertEquals(new TaskResultCollection(), $result);

        $this->runnerReporter->start($context)->shouldBeCalled();
        $this->runnerReporter->finish($context, $result)->shouldBeCalled();
    }
}
