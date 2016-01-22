<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
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

    function it_should_sort_on_priority(TaskInterface $task1, TaskInterface $task2, TaskInterface $task3, GrumPHP $grumPHP)
    {
        $this->beConstructedWith(array($task1, $task2, $task3));

        $task1->getName()->willReturn('task1');
        $task2->getName()->willReturn('task2');
        $task3->getName()->willReturn('task3');

        $grumPHP->getTaskMetadata('task1')->willReturn(array('priority' => 100));
        $grumPHP->getTaskMetadata('task2')->willReturn(array('priority' => 200));
        $grumPHP->getTaskMetadata('task3')->willReturn(array('priority' => 100));

        $result = $this->sortByPriority($grumPHP);
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\TasksCollection');
        $result->count()->shouldBe(3);
        $tasks = $result->toArray();

        $tasks[0]->shouldBe($task2);
        $tasks[1]->shouldBe($task1);
        $tasks[2]->shouldBe($task3);
    }
}
