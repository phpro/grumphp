<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
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

    function it_is_passed_if_it_contains_only_passed_task_result(TaskInterface $task, ContextInterface $context)
    {
        $aTaskResult = new TaskResult(TaskResult::PASSED, $task->getWrappedObject(), $context->getWrappedObject());
        $this->add($aTaskResult);
        $anotherTaskResult = new TaskResult(TaskResult::PASSED, $task->getWrappedObject(), $context->getWrappedObject());
        $this->add($anotherTaskResult);

        $this->isPassed()->shouldBe(true);
    }

    function it_is_not_passed_if_it_contains_a_failed_task_result(TaskInterface $task, ContextInterface $context)
    {
        $aTaskResult = new TaskResult(TaskResult::PASSED, $task->getWrappedObject(), $context->getWrappedObject());
        $this->add($aTaskResult);
        $anotherTaskResult = new TaskResult(TaskResult::FAILED, $task->getWrappedObject(), $context->getWrappedObject());
        $this->add($anotherTaskResult);

        $this->isPassed()->shouldBe(false);
    }

    function it_is_not_passed_if_it_does_not_contains_any_task()
    {
        $this->isPassed()->shouldBe(false);
    }

    function it_returns_passed_code_if_it_contains_only_passed_task_result(TaskInterface $task, ContextInterface $context)
    {
        $aTaskResult = new TaskResult(TaskResult::PASSED, $task->getWrappedObject(), $context->getWrappedObject());
        $this->add($aTaskResult);
        $anotherTaskResult = new TaskResult(TaskResult::PASSED, $task->getWrappedObject(), $context->getWrappedObject());
        $this->add($anotherTaskResult);

        $this->getResultCode()->shouldBe(TaskResult::PASSED);
    }

    function it_returns_failed_code_if_it_contains_a_failed_task_result(TaskInterface $task, ContextInterface $context)
    {
        $aTaskResult = new TaskResult(TaskResult::PASSED, $task->getWrappedObject(), $context->getWrappedObject());
        $this->add($aTaskResult);
        $anotherTaskResult = new TaskResult(TaskResult::FAILED, $task->getWrappedObject(), $context->getWrappedObject());
        $this->add($anotherTaskResult);

        $this->getResultCode()->shouldBe(TaskResult::FAILED);
    }

    function it_returns_no_task_code_if_it_does_not_contains_any_task()
    {
        $this->getResultCode()->shouldBe(TaskResultCollection::NO_TASKS);
    }

    function it_filters_by_result_code(TaskInterface $task, ContextInterface $context)
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $this->add(new TaskResult(TaskResult::PASSED, $aTask, $aContext));
        $this->add(new TaskResult(TaskResult::PASSED, $aTask, $aContext));
        $this->add(new TaskResult(TaskResult::FAILED, $aTask, $aContext));

        $this->filterByResultCode(TaskResult::PASSED)->shouldHaveCount(2);
        $this->filterByResultCode(TaskResult::FAILED)->shouldHaveCount(1);
        $this->filterByResultCode(TaskResult::NONBLOCKING_FAILED)->shouldHaveCount(0);
    }

    function it_returns_all_task_result_messages(TaskInterface $task, ContextInterface $context)
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $this->add(new TaskResult(TaskResult::FAILED, $aTask, $aContext, 'failed message'));
        $this->add(new TaskResult(TaskResult::PASSED, $aTask, $aContext));
        $this->add(new TaskResult(TaskResult::FAILED, $aTask, $aContext, 'another failed message'));

        $this->getAllMessages()->shouldReturn(array(
            'failed message',
            null,
            'another failed message',
        ));
    }
}
