<?php

namespace spec\GrumPHP\Runner;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\RunnerFailedEvent;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Event\TaskFailedEvent;
use GrumPHP\Exception\PlatformException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskRunner;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class TaskRunnerSpec
 */
class TaskRunnerSpec extends ObjectBehavior
{

    public function let(
        GrumPHP $grumPHP,
        EventDispatcherInterface $eventDispatcher,
        TaskInterface $task1,
        TaskInterface $task2,
        ContextInterface $context
    ) {
        $this->beConstructedWith($grumPHP, $eventDispatcher);

        $task1->getName()->willReturn('task1');
        $task1->canRunInContext($context)->willReturn(true);
        $task1->run($context)->willReturn(TaskResult::createPassed($task1->getWrappedObject(), $context->getWrappedObject()));
        $task2->getName()->willReturn('task2');
        $task2->canRunInContext($context)->willReturn(true);
        $task2->run($context)->willReturn(TaskResult::createPassed($task2->getWrappedObject(), $context->getWrappedObject()));

        $grumPHP->stopOnFailure()->willReturn(false);
        $grumPHP->getTaskMetadata('task1')->willReturn(['priority' => 0]);
        $grumPHP->getTaskMetadata('task2')->willReturn(['priority' => 0]);
        $grumPHP->isBlockingTask('task1')->willReturn(true);
        $grumPHP->isBlockingTask('task2')->willReturn(true);

        $this->addTask($task1);
        $this->addTask($task2);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TaskRunner::class);
    }

    function it_holds_tasks(TaskInterface $task1, TaskInterface $task2)
    {
        $this->getTasks()->toArray()->shouldEqual([$task1, $task2]);
    }

    function it_does_not_add_the_same_task_twice(TaskInterface $task1, TaskInterface $task2)
    {
        $this->addTask($task1);

        $this->getTasks()->toArray()->shouldEqual([$task1, $task2]);
    }

    function it_runs_all_tasks(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->run($context)->shouldBeCalled();
        $task2->run($context)->shouldBeCalled();

        $this->run($context);
    }

    function it_returns_a_passed_tasks_result_if_all_tasks_passed(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->run($context)->shouldBeCalled();
        $task2->run($context)->shouldBeCalled();

        $this->run($context)->shouldReturnAnInstanceOf(TaskResultCollection::class);
        $this->run($context)->shouldBePassed();
    }

    function it_returns_a_failed_tasks_result_if_a_task_fails(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->run($context)->willReturn(TaskResult::createFailed($task1->getWrappedObject(), $context->getWrappedObject(), ''));
        $task2->run($context)->shouldBeCalled();

        $this->run($context)->shouldReturnAnInstanceOf(TaskResultCollection::class);
        $this->run($context)->shouldNotBePassed();
        $this->run($context)->shouldContainFailedTaskResult();
    }

    function it_returns_a_failed_tasks_throws_an_exception(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->run($context)->willThrow(RuntimeException::class);
        $task2->run($context)->shouldBeCalled();

        $this->run($context)->shouldReturnAnInstanceOf(TaskResultCollection::class);
        $this->run($context)->shouldNotBePassed();
        $this->run($context)->shouldContainFailedTaskResult();
    }

    function it_returns_non_blocking_faled_when_tasks_throws_a_platform_exception(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->run($context)->willThrow(PlatformException::class);
        $task2->run($context)->shouldBeCalled();

        $this->run($context)->shouldReturnAnInstanceOf(TaskResultCollection::class);
        $this->run($context)->shouldNotBePassed();
        $this->run($context)->shouldContainNonBlockingFailedTaskResult();
    }

    function it_returns_a_failed_tasks_result_if_a_non_blocking_task_fails(GrumPHP $grumPHP, TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $grumPHP->isBlockingTask('task1')->willReturn(false);
        $task1->run($context)->willReturn(TaskResult::createFailed($task1->getWrappedObject(), $context->getWrappedObject(), ''));
        $task2->run($context)->shouldBeCalled();

        $this->run($context)->shouldReturnAnInstanceOf(TaskResultCollection::class);
        $this->run($context)->shouldNotBePassed();
        $this->run($context)->shouldContainNonBlockingFailedTaskResult();
    }

    function it_runs_subsequent_tasks_if_one_fails(
        TaskInterface $task1,
        TaskInterface $task2,
        ContextInterface $context
    ) {
        $task1->run($context)->willReturn(TaskResult::createFailed($task1->getWrappedObject(), $context->getWrappedObject(), ''));
        $task2->run($context)->shouldBeCalled();

        $this->run($context);
    }

    function it_stops_on_a_failed_task_if_stop_on_failure(GrumPHP $grumPHP, TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $grumPHP->stopOnFailure()->willReturn(true);
        $task1->run($context)->willReturn(TaskResult::createFailed($task1->getWrappedObject(), $context->getWrappedObject(), ''));
        $task2->run($context)->shouldNotBeCalled();

        $this->run($context)->shouldHaveCount(1);
    }

    function it_does_not_stop_on_a_non_blocking_failed_task_if_stop_on_failure(GrumPHP $grumPHP, TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $grumPHP->stopOnFailure()->willReturn(true);
        $grumPHP->isBlockingTask('task1')->willReturn(false);
        $task1->run($context)->willReturn(TaskResult::createFailed($task1->getWrappedObject(), $context->getWrappedObject(), ''));
        $task2->run($context)->shouldBeCalled();

        $this->run($context)->shouldHaveCount(2);
    }

    function it_triggers_events_during_happy_flow(
        EventDispatcherInterface $eventDispatcher,
        TaskInterface $task1,
        TaskInterface $task2,
        ContextInterface $context
    ) {
        $task1->run($context)->shouldBeCalled();
        $task2->run($context)->shouldBeCalled();

        $eventDispatcher->dispatch(RunnerEvents::RUNNER_RUN, Argument::type(RunnerEvent::class))->shouldBeCalled();
        $eventDispatcher->dispatch(TaskEvents::TASK_RUN, Argument::type(TaskEvent::class))->shouldBeCalled();
        $eventDispatcher->dispatch(TaskEvents::TASK_COMPLETE, Argument::type(TaskEvent::class))->shouldBeCalled();
        $eventDispatcher->dispatch(RunnerEvents::RUNNER_COMPLETE, Argument::type(RunnerEvent::class))->shouldBeCalled();

        $this->run($context);
    }

    function it_triggers_events_during_error_flow(
        EventDispatcherInterface $eventDispatcher,
        TaskInterface $task1,
        TaskInterface $task2,
        ContextInterface $context
    ) {
        $task1->run($context)->willReturn(TaskResult::createFailed($task1->getWrappedObject(), $context->getWrappedObject(), ''));
        $task2->run($context)->willReturn(TaskResult::createFailed($task1->getWrappedObject(), $context->getWrappedObject(), ''));

        $eventDispatcher->dispatch(RunnerEvents::RUNNER_RUN, Argument::type(RunnerEvent::class))->shouldBeCalled();
        $eventDispatcher->dispatch(TaskEvents::TASK_RUN, Argument::type(TaskEvent::class))->shouldBeCalled();
        $eventDispatcher->dispatch(TaskEvents::TASK_FAILED, Argument::type(TaskFailedEvent::class))->shouldBeCalled();
        $eventDispatcher->dispatch(RunnerEvents::RUNNER_FAILED, Argument::type(RunnerFailedEvent::class))->shouldBeCalled();

        $this->run($context);
    }

    public function getMatchers()
    {
        return [
            'containFailedTaskResult' => function ($taskResultCollection) {
                return $taskResultCollection->exists(function ($key, $taskResult) {
                    return TaskResult::FAILED === $taskResult->getResultCode();
                });
            },
            'containNonBlockingFailedTaskResult' => function ($taskResultCollection) {
                return $taskResultCollection->exists(function ($key, $taskResult) {
                    return TaskResult::NONBLOCKING_FAILED === $taskResult->getResultCode();
                });
            },
        ];
    }
}
