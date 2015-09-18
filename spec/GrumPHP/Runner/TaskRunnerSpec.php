<?php

namespace spec\GrumPHP\Runner;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TaskRunnerSpec extends ObjectBehavior
{

    public function let(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->canRunInContext($context)->willReturn(true);
        $task2->canRunInContext($context)->willReturn(true);

        $this->addTask($task1);
        $this->addTask($task2);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Runner\TaskRunner');
    }

    function it_holds_tasks(TaskInterface $task1, TaskInterface $task2)
    {
        $this->getTasks()->toArray()->shouldEqual(array($task1, $task2));
    }

    function it_does_not_add_the_same_task_twice(TaskInterface $task1, TaskInterface $task2)
    {
        $this->addTask($task1);

        $this->getTasks()->toArray()->shouldEqual(array($task1, $task2));
    }

    function it_runs_all_tasks(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->run($context)->shouldBeCalled();
        $task2->run($context)->shouldBeCalled();

        $this->run($context);
    }

    function it_throws_exception_if_task_fails(TaskInterface $task1, TaskInterface $task2, ContextInterface $context)
    {
        $task1->run($context)->willThrow('GrumPHP\Exception\RuntimeException');
        $task2->run($context)->shouldBeCalled();

        $this->shouldThrow('GrumPHP\Exception\FailureException')->duringRun($context);
    }

    function it_runs_subsequent_tasks_if_one_fails(
        TaskInterface $task1,
        TaskInterface $task2,
        ContextInterface $context
    ) {
        $task1->run($context)->willThrow('GrumPHP\Exception\RuntimeException');
        $task2->run($context)->shouldBeCalled();

        $this->shouldThrow('GrumPHP\Exception\FailureException')->duringRun($context);
    }
}
