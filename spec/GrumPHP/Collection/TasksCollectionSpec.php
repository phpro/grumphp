<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use phpDocumentor\Reflection\DocBlock\Context;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TasksCollectionSpec extends ObjectBehavior
{
    public function let(TaskInterface $task1, TaskInterface $task2)
    {
        $this->beConstructedWith(array($task1, $task2));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Collection\TasksCollection');
    }

    function it_is_an_array_collection()
    {
        $this->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
    }

    function it_should_filter_by_context(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->canRunInContext($context)->willReturn(true);
        $task2->canRunInContext($context)->willReturn(false);

        $result = $this->filterByContext($context);
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\TasksCollection');
        $result->count()->shouldBe(1);
        $tasks = $result->toArray();
        $tasks[0]->shouldBe($task1);
    }

}
