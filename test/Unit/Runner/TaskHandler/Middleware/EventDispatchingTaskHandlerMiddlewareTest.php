<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\TaskHandler\Middleware;

use GrumPHP\Event\Dispatcher\EventDispatcherInterface;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Event\TaskFailedEvent;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskHandler\Middleware\EventDispatchingTaskHandlerMiddleware;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Test\Runner\AbstractTaskHandlerMiddlewareTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class EventDispatchingTaskHandlerMiddlewareTest extends AbstractTaskHandlerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var EventDispatchingTaskHandlerMiddleware
     */
    private $middleware;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->middleware = new EventDispatchingTaskHandlerMiddleware(
            $this->eventDispatcher->reveal()
        );
    }

    /** @test */
    public function it_can_dispatch_events_on_success(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $taskContext = $context->getTaskContext();
        $expectedResult = TaskResult::createPassed($task, $taskContext);
        $next = $this->createNextResultCallback($expectedResult);

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($actualResult, $expectedResult);

        $this->eventDispatcher->dispatch(
            Argument::that(function(TaskEvent $event) use ($task, $taskContext): bool {
                return $event->getTask() === $task
                    && $event->getContext() === $taskContext;
            }),
            TaskEvents::TASK_RUN
        )->shouldBeCalled();
        $this->eventDispatcher->dispatch(
            Argument::that(function(TaskEvent $event) use ($task, $taskContext): bool {
                return $event->getTask() === $task
                    && $event->getContext() === $taskContext;
            }),
            TaskEvents::TASK_COMPLETE
        )->shouldBeCalled();
    }

    /** @test */
    public function it_can_dispatch_events_on_skipped(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $taskContext = $context->getTaskContext();
        $expectedResult = TaskResult::createSkipped($task, $taskContext);
        $next = $this->createNextResultCallback($expectedResult);

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($actualResult, $expectedResult);

        $this->eventDispatcher->dispatch(
            Argument::that(function(TaskEvent $event) use ($task, $taskContext): bool {
                return $event->getTask() === $task
                   && $event->getContext() === $taskContext;
            }),
            TaskEvents::TASK_RUN
        )->shouldBeCalled();
        $this->eventDispatcher->dispatch(
            Argument::that(function(TaskEvent $event) use ($task, $taskContext): bool {
                return $event->getTask() === $task
                   && $event->getContext() === $taskContext;
            }),
            TaskEvents::TASK_SKIPPED
        )->shouldBeCalled();
    }

    /** @test */
    public function it_can_dispatch_events_on_failed(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $taskContext = $context->getTaskContext();
        $expectedResult = TaskResult::createFailed($task, $taskContext, 'error');
        $next = $this->createNextResultCallback($expectedResult);

        $promise = $this->middleware->handle($task, $context, $next);
        $actualResult = $this->resolve($promise);

        self::assertSame($actualResult, $expectedResult);

        $this->eventDispatcher->dispatch(
            Argument::that(function(TaskEvent $event) use ($task, $taskContext): bool {
                return $event->getTask() === $task
                   && $event->getContext() === $taskContext;
            }),
            TaskEvents::TASK_RUN
        )->shouldBeCalled();
        $this->eventDispatcher->dispatch(
            Argument::that(function(TaskEvent $event) use ($task, $taskContext): bool {
                return $event instanceof TaskFailedEvent
                    && $event->getTask() === $task
                    && $event->getContext() === $taskContext
                    && $event->getException()->getMessage() === 'error';
            }),
            TaskEvents::TASK_FAILED
        )->shouldBeCalled();
    }

}
