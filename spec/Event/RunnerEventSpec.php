<?php

namespace spec\GrumPHP\Event;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Task\Context\ContextInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class RunnerEventSpec extends ObjectBehavior
{
    public function let(TasksCollection $tasks, ContextInterface $context, TaskResultCollection $taskResults)
    {
        $this->beConstructedWith($tasks, $context, $taskResults);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RunnerEvent::class);
    }

    public function it_is_an_event()
    {
        $this->shouldHaveType(Event::class);
    }

    public function it_has_tasks(TasksCollection $tasks)
    {
        $this->getTasks()->shouldBe($tasks);
    }

    public function it_should_have_a_context(ContextInterface $context)
    {
        $this->getContext()->shouldBe($context);
    }

    public function it_should_have_a_task_result_collection(TaskResultCollection $taskResults)
    {
        $this->getTaskResults()->shouldBe($taskResults);
    }
}
