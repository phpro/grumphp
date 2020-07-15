<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Fixer\FixerUpper;
use GrumPHP\Runner\Middleware\FixCodeMiddleware;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Test\Runner\AbstractRunnerMiddlewareTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class FixCodeMiddlewareTest extends AbstractRunnerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var FixCodeMiddleware
     */
    private $middleware;

    /**
     * @var ObjectProphecy|FixerUpper
     */
    private $fixerUpper;

    protected function setUp(): void
    {
        $this->fixerUpper = $this->prophesize(FixerUpper::class);
        $this->middleware = new FixCodeMiddleware(
            $this->fixerUpper->reveal()
        );
    }

    /** @test */
    public function it_can_fix_broken_tasks(): void
    {
        $context = $this->createRunnerContext();
        $next = static function (TaskRunnerContext $passedContext) {
            return new TaskResultCollection();
        };

        $result = $this->middleware->handle($context, $next);
        self::assertEquals(new TaskResultCollection(), $result);

        $this->fixerUpper->fix($result)->shouldBeCalled();
    }
}
