<?php

namespace spec\GrumPHP\Event;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Task\Context\ContextInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RunnerFailedEventSpec extends ObjectBehavior
{
    function let(TasksCollection $tasks, ContextInterface $context)
    {
        $this->beConstructedWith($tasks, $context, array());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Event\RunnerFailedEvent');
    }

    function it_is_a_runner_event()
    {
        $this->shouldHaveType('GrumPHP\Event\RunnerEvent');
    }

    function it_has_tasks(TasksCollection $tasks)
    {
        $this->getTasks()->shouldBe($tasks);
    }

    function it_should_have_a_context(ContextInterface $context)
    {
        $this->getContext()->shouldBe($context);
    }

    function it_should_contain_the_error_messages()
    {
        $this->getMessages()->shouldBe(array());
    }
}
