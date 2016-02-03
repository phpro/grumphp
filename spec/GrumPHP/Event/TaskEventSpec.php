<?php

namespace spec\GrumPHP\Event;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TaskEventSpec extends ObjectBehavior
{

    function let(TaskInterface $task, ContextInterface $context)
    {
        $this->beConstructedWith($task, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Event\TaskEvent');
    }

    function it_is_an_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
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
