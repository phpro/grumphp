<?php

namespace spec\GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use GrumPHP\TestSuite\TestSuiteInterface;
use PhpSpec\ObjectBehavior;

class TasksCollectionSpec extends ObjectBehavior
{
    public function let(TaskInterface $task1, TaskInterface $task2)
    {
        $this->beConstructedWith([$task1, $task2]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TasksCollection::class);
    }

    function it_is_an_array_collection()
    {
        $this->shouldHaveType(ArrayCollection::class);
    }

    function it_should_filter_by_context(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->canRunInContext($context)->willReturn(true);
        $task2->canRunInContext($context)->willReturn(false);

        $result = $this->filterByContext($context);
        $result->shouldBeAnInstanceOf(TasksCollection::class);
        $result->count()->shouldBe(1);
        $tasks = $result->toArray();
        $tasks[0]->shouldBe($task1);
    }

    function it_can_filter_by_testsuite(TaskInterface $task1, TaskInterface $task2, TestSuiteInterface $testSuite)
    {
        $task1->getConfig()->willReturn(new TaskConfig('task1', [], new Metadata([])));
        $task2->getConfig()->willReturn(new TaskConfig('task2', [], new Metadata([])));
        $testSuite->getTaskNames()->willReturn(['task1']);

        $result = $this->filterByTestSuite($testSuite);
        $result->shouldBeAnInstanceOf(TasksCollection::class);
        $result->count()->shouldBe(1);
        $tasks = $result->toArray();
        $tasks[0]->shouldBe($task1);
    }

    function it_can_filter_by_empty_testsuite(TaskInterface $task1, TaskInterface $task2)
    {
        $result = $this->filterByTestSuite(null);
        $result->shouldBeAnInstanceOf(TasksCollection::class);
        $result->count()->shouldBe(2);
        $tasks = $result->toArray();
        $tasks[0]->shouldBe($task1);
        $tasks[1]->shouldBe($task2);
    }

    function it_can_filter_by_task_names(TaskInterface $task1, TaskInterface $task2)
    {
        $task1->getConfig()->willReturn(new TaskConfig('task1', [], new Metadata([])));
        $task2->getConfig()->willReturn(new TaskConfig('task2', [], new Metadata([])));
        $tasks = ['task1'];

        $result = $this->filterByTaskNames($tasks);
        $result->shouldBeAnInstanceOf(TasksCollection::class);
        $result->count()->shouldBe(1);
        $tasks = $result->toArray();
        $tasks[0]->shouldBe($task1);
    }

    function it_can_filter_by_duplicate_task_names(TaskInterface $task1, TaskInterface $task2)
    {
        $task1->getConfig()->willReturn(new TaskConfig('task1', [], new Metadata([])));
        $task2->getConfig()->willReturn(new TaskConfig('task2', [], new Metadata([])));
        $tasks = ['task1', 'task1'];

        $result = $this->filterByTaskNames($tasks);
        $result->shouldBeAnInstanceOf(TasksCollection::class);
        $result->count()->shouldBe(1);
        $tasks = $result->toArray();
        $tasks[0]->shouldBe($task1);
    }

    function it_can_filter_by_empty_task_names(TaskInterface $task1, TaskInterface $task2)
    {
        $task1->getConfig()->willReturn(new TaskConfig('task1', [], new Metadata([])));
        $task2->getConfig()->willReturn(new TaskConfig('task2', [], new Metadata([])));
        $tasks = [];

        $result = $this->filterByTaskNames($tasks);
        $result->shouldBeAnInstanceOf(TasksCollection::class);
        $result->count()->shouldBe(2);
        $tasks = $result->toArray();
        $tasks[0]->shouldBe($task1);
        $tasks[1]->shouldBe($task2);
    }

    function it_should_sort_on_priority(TaskInterface $task1, TaskInterface $task2, TaskInterface $task3)
    {
        $this->beConstructedWith([$task1, $task2, $task3]);

        $task1->getConfig()->willReturn(new TaskConfig('task1', [], new Metadata(['priority' => 100])));
        $task2->getConfig()->willReturn(new TaskConfig('task2', [], new Metadata(['priority' => 200])));
        $task3->getConfig()->willReturn(new TaskConfig('task3', [], new Metadata(['priority' => 100])));

        $result = $this->sortByPriority();
        $result->shouldBeAnInstanceOf(TasksCollection::class);
        $result->count()->shouldBe(3);
        $tasks = $result->toArray();

        $tasks[0]->shouldBe($task2);
        $tasks[1]->shouldBe($task1);
        $tasks[2]->shouldBe($task3);
    }

    function it_should_group_by_priority(TaskInterface $task1, TaskInterface $task2, TaskInterface $task3)
    {
        $this->beConstructedWith([$task1, $task2, $task3]);

        $task1->getConfig()->willReturn(new TaskConfig('task1', [], new Metadata(['priority' => 100])));
        $task2->getConfig()->willReturn(new TaskConfig('task2', [], new Metadata(['priority' => 200])));
        $task3->getConfig()->willReturn(new TaskConfig('task3', [], new Metadata(['priority' => 100])));

        $result = $this->groupByPriority();
        $result[100]->shouldBeAnInstanceOf(TasksCollection::class);
        $result[100]->count()->shouldBe(2);
        $result[100]->toArray()->shouldBe([$task1, $task3]);
        $result[200]->shouldBeAnInstanceOf(TasksCollection::class);
        $result[200]->count()->shouldBe(1);
        $result[200]->toArray()->shouldBe([$task2]);
    }
}
