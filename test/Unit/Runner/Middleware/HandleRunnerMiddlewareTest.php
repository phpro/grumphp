<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Middleware;

use Amp\Future;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\Model\RunnerConfig;
use GrumPHP\Runner\Middleware\HandleRunnerMiddleware;
use GrumPHP\Runner\StopOnFailure;
use GrumPHP\Runner\TaskHandler\TaskHandler;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Runner\AbstractRunnerMiddlewareTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use function Amp\async;
use function Amp\delay;

class HandleRunnerMiddlewareTest extends AbstractRunnerMiddlewareTestCase
{
    use ProphecyTrait;

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

        $this->taskHandler->handle(
            Argument::type(TaskInterface::class),
            $context,
            Argument::type(StopOnFailure::class)
        )->will(function (array $arguments) {
            return Future::complete(TaskResult::createPassed($arguments[0], $arguments[1]->getTaskContext()));
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

        $this->taskHandler->handle(
            Argument::type(TaskInterface::class),
            $context,
            Argument::type(StopOnFailure::class)
        )->will(function (array $arguments) {
            return async(function () use ($arguments): TaskResultInterface {
                delay(((int)($arguments[0]->getConfig()->getName()))/10);

                return TaskResult::createFailed($arguments[0], $arguments[1]->getTaskContext(), 'error');
            });
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

        $this->taskHandler->handle(
            Argument::type(TaskInterface::class),
            $context,
            Argument::type(StopOnFailure::class)
        )->will(function (array $arguments) {
            return async(function () use ($arguments): TaskResultInterface {
                $id = (int)($arguments[0]->getConfig()->getName());
                delay($id/10);

                if ($id === 2) {
                    $arguments[2]->stop();
                }

                return TaskResult::createFailed($arguments[0], $arguments[1]->getTaskContext(), 'error');
            });
        });

        $result = $this->middleware->handle($context, $next);
        self::assertGreaterThanOrEqual(1, count($result));
        self::assertSame(2, count($result));
        self::assertTrue($result->exists(static function ($key, TaskResultInterface $result) use ($task1) : bool {
            return $result->getTask() === $task1;
        }));
        self::assertTrue($result->isFailed());
    }

    /** @test */
    public function it_does_not_skip_on_non_blocking_failed_result(): void
    {
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

        $this->taskHandler->handle(
            Argument::type(TaskInterface::class),
            $context,
            Argument::type(StopOnFailure::class)
        )->will(function (array $arguments) {
            return async(function () use ($arguments): TaskResultInterface {
                delay(((int)($arguments[0]->getConfig()->getName()))/10);

                return TaskResult::createNonBlockingFailed($arguments[0], $arguments[1]->getTaskContext(), 'error');
            });
        });

        $result = $this->middleware->handle($context, $next);
        self::assertSame(3, count($result));
        self::assertSame(3, $result->filterByResultCode(TaskResultInterface::NONBLOCKING_FAILED)->count());
        self::assertFalse($result->isFailed());
    }

    /** @test */
    public function it_rethrows_exception_on_unkown_exception(): void
    {
        $context = $this->createRunnerContext()->withTasks(new TasksCollection([
            $task1 = $this->mockTask('1'),
            $task2 = $this->mockTask('2'),
            $task3 = $this->mockTask('3'),
        ]));
        $next = $this->createNextShouldNotBeCalledCallback();

        $this->taskHandler->handle(
            Argument::type(TaskInterface::class),
            $context,
            Argument::type(StopOnFailure::class)
        )->will(function (array $arguments) {
            return Future::error(new \RuntimeException('nope'));
        });

        $this->expectException(\RuntimeException::class);
        $this->middleware->handle($context, $next);
    }
}
