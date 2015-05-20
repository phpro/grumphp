<?php

namespace spec\GrumPHP\Runner;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TaskRunnerSpec extends ObjectBehavior
{

    protected $files;

    public function let()
    {
        $this->files = new FilesCollection();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Runner\TaskRunner');
    }

    function it_holds_tasks(TaskInterface $task1, TaskInterface $task2)
    {
        $this->addTask($task1);
        $this->addTask($task2);

        $this->getTasks()->shouldEqual(array($task1, $task2));
    }

    function it_does_not_add_the_same_task_twice(TaskInterface $task1, TaskInterface $task2)
    {
        $this->addTask($task1);
        $this->addTask($task1);
        $this->addTask($task2);

        $this->getTasks()->shouldEqual(array($task1, $task2));
    }

    function it_runs_all_enabled_tasks(TaskInterface $task1, TaskInterface $task2)
    {
        $task1->isEnabled()->willReturn(true);
        $task2->isEnabled()->willReturn(false);

        $this->addTask($task1);
        $this->addTask($task2);

        $task1->run($this->files)->shouldBeCalled();
        $task2->run($this->files)->shouldNotBeCalled();

        $this->run($this->files);
    }

    function it_throws_exception_if_task_fails(TaskInterface $task1)
    {
        $task1->isEnabled()->willReturn(true);

        $this->addTask($task1);

        $task1->run($this->files)->willThrow('GrumPHP\Exception\RuntimeException');

        $this->shouldThrow('GrumPHP\Exception\FailureException')->duringRun($this->files);
    }

    function it_runs_subsequent_tasks_if_one_fails(TaskInterface $task1, TaskInterface $task2)
    {
        $task1->isEnabled()->willReturn(true);
        $task2->isEnabled()->willReturn(true);

        $this->addTask($task1);
        $this->addTask($task2);

        $task1->run($this->files)->willThrow('GrumPHP\Exception\RuntimeException');
        $task2->run($this->files)->shouldBeCalled();

        $this->shouldThrow('GrumPHP\Exception\FailureException')->duringRun($this->files);
    }
}
