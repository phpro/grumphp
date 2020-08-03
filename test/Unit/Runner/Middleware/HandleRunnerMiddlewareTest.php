<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Middleware;

use Amp\Delayed;
use Amp\Failure;
use Amp\Loop;
use Amp\Loop\DriverFactory;
use Amp\MultiReasonException;
use Amp\Success;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\Model\RunnerConfig;
use GrumPHP\Runner\Middleware\HandleRunnerMiddleware;
use GrumPHP\Runner\TaskHandler\TaskHandler;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Runner\AbstractRunnerMiddlewareTestCase;
use GrumPHPTest\Unit\Runner\Promise\LoopResettingTrait;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class HandleRunnerMiddlewareTest extends AbstractRunnerMiddlewareTestCase
{
    use ProphecyTrait;
    use LoopResettingTrait;

    /**
     * @var ObjectProphecy|TaskHandler
     */
    private $taskHandler;

    /**
     * @var HandleRunnerMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $this->taskHandler = $this->prophesize(TaskHandler::class);
        $this->middleware = new HandleRunnerMiddleware(
            $this->taskHandler->reveal(),
            new RunnerConfig($stopOnFailure = false, $hideCircumventionTip = false, $additionalInfo = '')
        );
    }

    /** @test */
    public function it_can_wait_for_results(): void
    {
        $context = $this->createRunnerContext()->withTasks(new TasksCollection([
            $task1 = $this->mockTask('task1'),
            $task2 = $this->mockTask('task2'),
            $task3 = $this->mockTask('task3'),
        ]));
        $next = $this->createNextShouldNotBeCalledCallback();

        $this->taskHandler->handle(Argument::type(TaskInterface::class), $context)->will(function (array $arguments) {
            return new Success(TaskResult::createPassed($arguments[0], $arguments[1]->getTaskContext()));
        });

        $result = $this->middleware->handle($context, $next);
        self::assertCount(3, $result);
        self::assertSame($result->get(0)->getTask(), $task1);
        self::assertSame($result->get(1)->getTask(), $task2);
        self::assertSame($result->get(2)->getTask(), $task3);
        self::assertTrue($result->isPassed());
    }

    /** @test */
    public function it_can_continue_on_failed_results(): void
    {
        $this->middleware = new HandleRunnerMiddleware(
            $this->taskHandler->reveal(),
            new RunnerConfig($stopOnFailure = false, $hideCircumventionTip = false, $additionalInfo = '')
        );

        $context = $this->createRunnerContext()->withTasks(new TasksCollection([
            $task1 = $this->mockTask('1'),
            $task2 = $this->mockTask('2'),
            $task3 = $this->mockTask('3'),
        ]));
        $next = $this->createNextShouldNotBeCalledCallback();

        $this->taskHandler->handle(Argument::type(TaskInterface::class), $context)->will(function (array $arguments) {
            return new Delayed(
                (int)($arguments[0]->getConfig()->getName()) * 10,
                TaskResult::createFailed($arguments[0], $arguments[1]->getTaskContext(), 'error')
            );
        });

        $result = $this->middleware->handle($context, $next);
        self::assertCount(3, $result);
        self::assertSame($result->get(0)->getTask(), $task1);
        self::assertSame($result->get(1)->getTask(), $task2);
        self::assertSame($result->get(2)->getTask(), $task3);
        self::assertTrue($result->isFailed());
    }

    /** @test */
    public function it_can_skip_on_first_failed_result(): void
    {
        $this->safelyRunAsync(function () {
            $this->middleware = new HandleRunnerMiddleware(
                $this->taskHandler->reveal(),
                new RunnerConfig($stopOnFailure = true, $hideCircumventionTip = false, $additionalInfo = '')
            );

            $context = $this->createRunnerContext()->withTasks(new TasksCollection([
                $task1 = $this->mockTask('1'),
                $task2 = $this->mockTask('2'),
                $task3 = $this->mockTask('3'),
            ]));
            $next = $this->createNextShouldNotBeCalledCallback();

            $this->taskHandler->handle(Argument::type(TaskInterface::class), $context)->will(function (array $arguments) {
                return new Delayed(
                    (int)($arguments[0]->getConfig()->getName()) * 100,
                    TaskResult::createFailed($arguments[0], $arguments[1]->getTaskContext(), 'error')
                );
            });

            $result = $this->middleware->handle($context, $next);
            self::assertCount(1, $result);
            self::assertSame($result->get(0)->getTask(), $task1);
            self::assertTrue($result->isFailed());
        });
    }

    /** @test */
    public function it_throws_multi_exception_on_unkown_exception(): void
    {
        $this->safelyRunAsync(function () {
            $context = $this->createRunnerContext()->withTasks(new TasksCollection([
                $task1 = $this->mockTask('1'),
                $task2 = $this->mockTask('2'),
                $task3 = $this->mockTask('3'),
            ]));
            $next = $this->createNextShouldNotBeCalledCallback();

            $this->taskHandler->handle(Argument::type(TaskInterface::class), $context)->willReturn(
                new Failure(new \RuntimeException('nope'))
            );

            $this->expectException(MultiReasonException::class);
            $this->middleware->handle($context, $next);
        });
    }
}
