<?php

namespace spec\GrumPHP\Event;

use GrumPHP\Collection\TasksCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RunnerEventSpec extends ObjectBehavior
{
    function let(TasksCollection $tasks)
    {
        $this->beConstructedWith($tasks);
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
}
