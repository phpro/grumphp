<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Runner\Middleware;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Event\Dispatcher\EventDispatcherInterface;
use GrumPHP\Event\Event;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\RunnerFailedEvent;
use GrumPHP\Runner\Middleware\EventDispatchingRunnerMiddleware;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunnerContext;
use GrumPHP\Test\Runner\AbstractRunnerMiddlewareTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class EventDispatchingRunnerMiddlewareTest extends AbstractRunnerMiddlewareTestCase
{
    use ProphecyTrait;

    /**
     * @var EventDispatchingRunnerMiddleware
     */
    private $middleware;

    /**
     * @var ObjectProphecy|EventDispatcherInterface
     */
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->middleware = new EventDispatchingRunnerMiddleware(
            $this->eventDispatcher->reveal()
        );
    }

    /** @test */
    public function it_should_dispatch_run_success_events(): void
    {
        $context = $this->createRunnerContext();
        $next = static function (TaskRunnerContext $passedContext) {
            return new TaskResultCollection();
        };

        $result = $this->middleware->handle($context, $next);
        self::assertEquals(new TaskResultCollection(), $result);

        $this->eventDispatcher->dispatch(
            Argument::that(function (Event $event) use ($context): bool {
                return $event instanceof RunnerEvent
                    && $event->getTasks() === $context->getTasks()
                    && $event->getContext() === $context->getTaskContext()
                    && $event->getTaskResults()->count() === 0;
            }),
            RunnerEvents::RUNNER_RUN
        )->shouldBeCalled();
        $this->eventDispatcher->dispatch(
            Argument::that(function (Event $event) use ($context): bool {
                return $event instanceof RunnerEvent
                       && $event->getTasks() === $context->getTasks()
                       && $event->getContext() === $context->getTaskContext()
                       && $event->getTaskResults()->count() === 0;
            }),
            RunnerEvents::RUNNER_COMPLETE
        )->shouldBeCalled();
    }

    /** @test */
    public function it_should_dispatch_run_failed_events(): void
    {
        $context = $this->createRunnerContext();
        $task = $this->mockTask('task');
        $next = function (TaskRunnerContext $passedContext) use ($task) {
            return new TaskResultCollection([
                TaskResult::createFailed(
                    $task,
                    $passedContext->getTaskContext(),
                    'failed'
                )
            ]);
        };

        $result = $this->middleware->handle($context, $next);
        self::assertEquals($next($context), $result);

        $this->eventDispatcher->dispatch(
            Argument::that(function (Event $event) use ($context): bool {
                return $event instanceof RunnerEvent
                       && $event->getTasks() === $context->getTasks()
                       && $event->getContext() === $context->getTaskContext()
                       && $event->getTaskResults()->count() === 0;
            }),
            RunnerEvents::RUNNER_RUN
        )->shouldBeCalled();
        $this->eventDispatcher->dispatch(
            Argument::that(function (Event $event) use ($context): bool {
                return $event instanceof RunnerFailedEvent
                       && $event->getTasks() === $context->getTasks()
                       && $event->getContext() === $context->getTaskContext()
                       && $event->getTaskResults()->count() === 1;
            }),
            RunnerEvents::RUNNER_FAILED
        )->shouldBeCalled();
    }
}
