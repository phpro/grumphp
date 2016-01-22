<?php

namespace spec\GrumPHP\Runner;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        $task2->getName()->willReturn('task2');
        $task2->canRunInContext($context)->willReturn(true);

        $grumPHP->stopOnFailure()->willReturn(false);
        $grumPHP->getTaskMetadata('task1')->willReturn(array('priority' => 0));
        $grumPHP->getTaskMetadata('task2')->willReturn(array('priority' => 0));

        $this->addTask($task1);
        $this->addTask($task2);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Runner\TaskRunner');
    }

    function it_holds_tasks(TaskInterface $task1, TaskInterface $task2)
    {
        $this->getTasks()->toArray()->shouldEqual(array($task1, $task2));
    }

    function it_does_not_add_the_same_task_twice(TaskInterface $task1, TaskInterface $task2)
    {
        $this->addTask($task1);

        $this->getTasks()->toArray()->shouldEqual(array($task1, $task2));
    }

    function it_runs_all_tasks(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->run($context)->shouldBeCalled();
        $task2->run($context)->shouldBeCalled();

        $this->run($context);
    }

    function it_throws_exception_if_task_fails(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->run($context)->willThrow('GrumPHP\Exception\RuntimeException');
        $task2->run($context)->shouldBeCalled();

        $this->shouldThrow('GrumPHP\Exception\FailureException')->duringRun($context);
    }

    function it_runs_subsequent_tasks_if_one_fails(
        TaskInterface $task1,
        TaskInterface $task2,
        ContextInterface $context
    ) {
        $task1->run($context)->willThrow('GrumPHP\Exception\RuntimeException');
        $task2->run($context)->shouldBeCalled();

        $this->shouldThrow('GrumPHP\Exception\FailureException')->duringRun($context);
    }

    function it_triggers_events_during_happy_flow(
        EventDispatcherInterface $eventDispatcher,
        TaskInterface $task1,
        TaskInterface $task2,
        ContextInterface $context
    ) {
        $task1->run($context)->shouldBeCalled();
        $task2->run($context)->shouldBeCalled();

        $eventDispatcher->dispatch(RunnerEvents::RUNNER_RUN, Argument::type('GrumPHP\Event\RunnerEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(TaskEvents::TASK_RUN, Argument::type('GrumPHP\Event\TaskEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(TaskEvents::TASK_COMPLETE, Argument::type('GrumPHP\Event\TaskEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(RunnerEvents::RUNNER_COMPLETE, Argument::type('GrumPHP\Event\RunnerEvent'))->shouldBeCalled();

        $this->run($context);
    }

    function it_triggers_events_during_error_flow(
        EventDispatcherInterface $eventDispatcher,
        TaskInterface $task1,
        TaskInterface $task2,
        ContextInterface $context
    ) {
        $task1->run($context)->willThrow('GrumPHP\Exception\RuntimeException');
        $task2->run($context)->willThrow('GrumPHP\Exception\RuntimeException');

        $eventDispatcher->dispatch(RunnerEvents::RUNNER_RUN, Argument::type('GrumPHP\Event\RunnerEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(TaskEvents::TASK_RUN, Argument::type('GrumPHP\Event\TaskEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(TaskEvents::TASK_FAILED, Argument::type('GrumPHP\Event\TaskFailedEvent'))->shouldBeCalled();
        $eventDispatcher->dispatch(RunnerEvents::RUNNER_FAILED, Argument::type('GrumPHP\Event\RunnerFailedEvent'))->shouldBeCalled();

        $this->shouldThrow('GrumPHP\Exception\FailureException')->duringRun($context);
    }
}
