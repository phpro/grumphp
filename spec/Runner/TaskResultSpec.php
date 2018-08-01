<?php

namespace spec\GrumPHP\Runner;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;

class TaskResultSpec extends ObjectBehavior
{
    const FAILED_TASK_MESSAGE = 'failed task message';

    function it_creates_passed_task(TaskInterface $task, ContextInterface $context)
    {
        $this->beConstructedThrough('createPassed', [$task, $context]);

        $this->getTask()->shouldBe($task);
        $this->getResultCode()->shouldBe(TaskResult::PASSED);
        $this->isPassed()->shouldBe(true);
        $this->getMessage()->shouldBe('');
    }

    function it_creates_failed_task(TaskInterface $task, ContextInterface $context)
    {
        $this->beConstructedThrough('createFailed', [$task, $context, self::FAILED_TASK_MESSAGE]);

        $this->getTask()->shouldBe($task);
        $this->getResultCode()->shouldBe(TaskResult::FAILED);
        $this->isPassed()->shouldBe(false);
        $this->getMessage()->shouldBe(self::FAILED_TASK_MESSAGE);
    }

    function it_creates_skipped_task(TaskInterface $task, ContextInterface $context)
    {
        $this->beConstructedThrough('createSkipped', [$task, $context]);

        $this->getTask()->shouldBe($task);
        $this->getResultCode()->shouldBe(TaskResult::SKIPPED);
        $this->isPassed()->shouldBe(false);
        $this->getMessage()->shouldBe('');
    }

    function it_should_be_a_blocking_task_if_it_is_a_failed_task(TaskInterface $task, ContextInterface $context)
    {
        $this->beConstructedThrough('createFailed', [$task, $context, self::FAILED_TASK_MESSAGE]);
        $this->isBlocking()->shouldBe(true);
    }

    function it_should_not_be_a_blocking_task_if_it_is_a_passed_task(TaskInterface $task, ContextInterface $context)
    {
        $this->beConstructedThrough('createPassed', [$task, $context, self::FAILED_TASK_MESSAGE]);
        $this->isBlocking()->shouldBe(false);
    }

    function it_should_not_be_a_blocking_task_if_it_is_a_non_blocking_failed_task(TaskInterface $task, ContextInterface $context)
    {
        $this->beConstructedThrough('createNonBlockingFailed', [$task, $context, self::FAILED_TASK_MESSAGE]);
        $this->isBlocking()->shouldBe(false);
    }
}
