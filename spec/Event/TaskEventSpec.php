<?php

namespace spec\GrumPHP\Event;

use GrumPHP\Event\TaskEvent;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\Event;

class TaskEventSpec extends ObjectBehavior
{
    function let(TaskInterface $task, ContextInterface $context)
    {
        $this->beConstructedWith($task, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TaskEvent::class);
    }

    function it_is_an_event()
    {
        $this->shouldHaveType(Event::class);
    }

    function it_has_a_task(TaskInterface $task)
    {
        $this->getTask()->shouldBe($task);
    }

    function it_should_have_a_context(ContextInterface $context)
    {
        $this->getContext()->shouldBe($context);
    }
}
