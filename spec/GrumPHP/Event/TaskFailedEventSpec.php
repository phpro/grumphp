<?php

namespace spec\GrumPHP\Event;

use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TaskFailedEventSpec extends ObjectBehavior
{


    function let(TaskInterface $task, \Exception $exception)
    {
        $this->beConstructedWith($task, $exception);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Event\TaskFailedEvent');
    }

    function it_is_a_runner_event()
    {
        $this->shouldHaveType('GrumPHP\Event\TaskEvent');
    }

    function it_has_a_task(TaskInterface $task)
    {
        $this->getTask()->shouldBe($task);
    }

    function it_should_contain_the_exception(\Exception $exception)
    {
        $this->getException()->shouldBe($exception);
    }
}
