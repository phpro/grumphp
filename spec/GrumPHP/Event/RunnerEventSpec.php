<?php

namespace spec\GrumPHP\Event;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Task\Context\ContextInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RunnerEventSpec extends ObjectBehavior
{
    function let(TasksCollection $tasks, ContextInterface $context)
    {
        $this->beConstructedWith($tasks, $context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Event\RunnerEvent');
    }

    function it_is_an_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    function it_has_tasks(TasksCollection $tasks)
    {
        $this->getTasks()->shouldBe($tasks);
    }

    function it_should_have_a_context(ContextInterface $context)
    {
        $this->getContext()->shouldBe($context);
    }
}
