<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Runner\TaskResult;
use PhpSpec\ObjectBehavior;

class TaskResultCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Collection\TaskResultCollection');
    }

    function it_contains_task_result(TaskResult $taskResult)
    {
        $this->add($taskResult);
        $this->add($taskResult);

        $this->getIterator()->shouldHaveCount(2);
        $this->getIterator()->current()->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResult');
        $this->getIterator()->next();
        $this->getIterator()->current()->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResult');
    }

    function it_is_passed_if_it_contains_only_passed_task_result(TaskResult $aTaskResult, TaskResult $anotherTaskResult)
    {
        $aTaskResult->isPassed()->willReturn(true);
        $this->add($aTaskResult);
        $anotherTaskResult->isPassed()->willReturn(true);
        $this->add($anotherTaskResult);

        $this->isPassed()->shouldBe(true);
    }

    function it_is_failed_if_it_contains_a_failed_task_result(TaskResult $aTaskResult, TaskResult $anotherTaskResult)
    {
        $aTaskResult->isPassed()->willReturn(true);
        $this->add($aTaskResult);
        $anotherTaskResult->isPassed()->willReturn(false);
        $this->add($anotherTaskResult);

        $this->isPassed()->shouldBe(false);
    }
}
