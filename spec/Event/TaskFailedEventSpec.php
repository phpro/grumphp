<?php

namespace spec\GrumPHP\Event;

use Exception;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskFailedEvent;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;

class TaskFailedEventSpec extends ObjectBehavior
{
    public function let(TaskInterface $task, ContextInterface $context, Exception $exception)
    {
        $this->beConstructedWith($task, $context, $exception);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TaskFailedEvent::class);
    }

    public function it_is_a_runner_event()
    {
        $this->shouldHaveType(TaskEvent::class);
    }

    public function it_has_a_task(TaskInterface $task)
    {
        $this->getTask()->shouldBe($task);
    }

    public function it_should_contain_the_exception(Exception $exception)
    {
        $this->getException()->shouldBe($exception);
    }

    public function it_should_have_a_context(ContextInterface $context)
    {
        $this->getContext()->shouldBe($context);
    }
}
