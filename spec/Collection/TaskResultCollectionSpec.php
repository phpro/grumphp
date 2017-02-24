<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;

class TaskResultCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(TaskResultCollection::class);
    }

    public function it_contains_task_result(TaskResult $taskResult)
    {
        $this->add($taskResult);
        $this->add($taskResult);

        $this->count()->shouldBe(2);
        $result = $this->toArray();
        $result[0]->shouldBe($taskResult);
        $result[1]->shouldBe($taskResult);
    }

    public function it_is_passed_if_it_contains_only_passed_task_result(TaskInterface $task, ContextInterface $context)
    {
        $this->add(TaskResult::createPassed($task->getWrappedObject(), $context->getWrappedObject()));
        $this->add(TaskResult::createPassed($task->getWrappedObject(), $context->getWrappedObject()));

        $this->isPassed()->shouldBe(true);
    }

    public function it_is_not_passed_if_it_contains_a_failed_task_result(TaskInterface $task, ContextInterface $context)
    {
        $this->add(TaskResult::createPassed($task->getWrappedObject(), $context->getWrappedObject()));
        $this->add(TaskResult::createFailed($task->getWrappedObject(), $context->getWrappedObject(), ''));

        $this->isPassed()->shouldBe(false);
    }

    public function it_is_not_passed_if_it_does_not_contains_any_task()
    {
        $this->isPassed()->shouldBe(false);
    }

    public function it_returns_passed_code_if_it_contains_only_passed_task_result(TaskInterface $task, ContextInterface $context)
    {
        $this->add(TaskResult::createPassed($task->getWrappedObject(), $context->getWrappedObject()));
        $this->add(TaskResult::createPassed($task->getWrappedObject(), $context->getWrappedObject()));

        $this->getResultCode()->shouldBe(TaskResult::PASSED);
    }

    public function it_returns_failed_code_if_it_contains_a_failed_task_result(TaskInterface $task, ContextInterface $context)
    {
        $this->add(TaskResult::createPassed($task->getWrappedObject(), $context->getWrappedObject()));
        $this->add(TaskResult::createFailed($task->getWrappedObject(), $context->getWrappedObject(), ''));

        $this->getResultCode()->shouldBe(TaskResult::FAILED);
    }

    public function it_returns_no_task_code_if_it_does_not_contains_any_task()
    {
        $this->getResultCode()->shouldBe(TaskResultCollection::NO_TASKS);
    }

    public function it_filters_by_result_code(TaskInterface $task, ContextInterface $context)
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $this->add(TaskResult::createPassed($aTask, $aContext));
        $this->add(TaskResult::createPassed($aTask, $aContext));
        $this->add(TaskResult::createFailed($aTask, $aContext, ''));

        $this->filterByResultCode(TaskResult::PASSED)->shouldHaveCount(2);
        $this->filterByResultCode(TaskResult::FAILED)->shouldHaveCount(1);
        $this->filterByResultCode(TaskResult::NONBLOCKING_FAILED)->shouldHaveCount(0);
    }

    public function it_returns_all_task_result_messages(TaskInterface $task, ContextInterface $context)
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $this->add(TaskResult::createFailed($aTask, $aContext, 'failed message'));
        $this->add(TaskResult::createPassed($aTask, $aContext));
        $this->add(TaskResult::createFailed($aTask, $aContext, 'another failed message'));

        $this->getAllMessages()->shouldReturn([
            'failed message',
            null,
            'another failed message',
        ]);
    }

    public function it_has_failed_if_it_contains_failed_task_result(TaskInterface $task, ContextInterface $context)
    {
        $aTask = $task->getWrappedObject();
        $aContext = $context->getWrappedObject();
        $this->add(TaskResult::createPassed($aTask, $aContext));
        $this->add(TaskResult::createNonBlockingFailed($aTask, $aContext, 'non blocking'));

        $this->isFailed()->shouldReturn(false);

        $this->add(TaskResult::createFailed($aTask, $aContext, 'failed message'));

        $this->isFailed()->shouldReturn(true);
    }
}
