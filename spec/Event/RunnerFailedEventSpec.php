<?php

namespace spec\GrumPHP\Event;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerFailedEvent;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use PhpSpec\ObjectBehavior;

class RunnerFailedEventSpec extends ObjectBehavior
{
    function let(TasksCollection $tasks, ContextInterface $context, TaskResultCollection $taskResults)
    {
        $this->beConstructedWith($tasks, $context, $taskResults);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RunnerFailedEvent::class);
    }

    function it_is_a_runner_event()
    {
        $this->shouldHaveType(RunnerEvent::class);
    }

    function it_has_tasks(TasksCollection $tasks)
    {
        $this->getTasks()->shouldBe($tasks);
    }

    function it_should_have_a_context(ContextInterface $context)
    {
        $this->getContext()->shouldBe($context);
    }

    function it_should_contain_the_error_messages(
        TasksCollection $tasks,
        ContextInterface $context,
        TaskResult $passedTaskResult,
        TaskResult $failedTaskResult
    ) {
        $failedTaskResult->getMessage()->willReturn('message 1');
        $passedTaskResult->getMessage()->willReturn('');

        $taskResults = new TaskResultCollection();
        $taskResults->add($passedTaskResult->getWrappedObject());
        $taskResults->add($failedTaskResult->getWrappedObject());

        $this->beConstructedWith($tasks, $context, $taskResults);

        $this->getMessages()->shouldReturn([
            'message 1',
        ]);
    }

    function it_should_have_a_task_result_collection(TaskResultCollection $taskResults)
    {
        $this->getTaskResults()->shouldBe($taskResults);
    }
}
