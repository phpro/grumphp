<?php

namespace spec\GrumPHP\Runner;

use GrumPHP\Fixer\FixResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Runner\FixableTaskResult;

class FixableTaskResultSpec extends ObjectBehavior
{
    public function let(TaskResultInterface $taskResult): void
    {
        $this->beConstructedWith($taskResult, function () {
            return FixResult::success('output');
        });
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType(FixableTaskResult::class);
    }

    public function it_is_a_task_result(): void
    {
        $this->shouldImplement(TaskResultInterface::class);
    }

    public function it_contains_task(TaskResultInterface $taskResult, TaskInterface $task): void
    {
        $taskResult->getTask()->willReturn($task);
        $this->getTask()->shouldBe($task);
    }

    public function it_contains_result_code(TaskResultInterface $taskResult): void
    {
        $taskResult->getResultCode()->willReturn(0);
        $this->getResultCode()->shouldBe(0);
    }

    public function it_knows_it_passed(TaskResultInterface $taskResult): void
    {
        $taskResult->isPassed()->willReturn(true);
        $this->isPassed()->shouldBe(true);
    }

    public function it_knows_it_failed(TaskResultInterface $taskResult): void
    {
        $taskResult->hasFailed()->willReturn(true);
        $this->hasFailed()->shouldBe(true);
    }

    public function it_knows_it_is_skipped(TaskResultInterface $taskResult): void
    {
        $taskResult->isSkipped()->willReturn(true);
        $this->isSkipped()->shouldBe(true);
    }

    public function it_knows_it_is_blocking(TaskResultInterface $taskResult): void
    {
        $taskResult->isBlocking()->willReturn(true);
        $this->isBlocking()->shouldBe(true);
    }

    public function it_contains_message(TaskResultInterface $taskResult): void
    {
        $taskResult->getMessage()->willReturn($message = 'message');
        $this->getMessage()->shouldBe($message);
    }

    public function it_contains_context(TaskResultInterface $taskResult, ContextInterface $context): void
    {
        $taskResult->getContext()->willReturn($context);
        $this->getContext()->shouldBe($context);
    }

    public function it_can_fix(): void
    {
        $result = $this->fix();
        $result->ok()->shouldBe(true);
        $result->result()->shouldBe('output');
    }

    function it_can_add_additional_message(TaskResultInterface $taskResult)
    {
        $taskResult->withAppendedMessage(Argument::type('string'))->will(function ($arguments) use ($taskResult) {
            $taskResult->getMessage()->willReturn($arguments[0]);

            return $taskResult;
        });

        $new = $this->withAppendedMessage($appended = 'appendedinfo');

        $new->shouldNotBe($this);
        $new->getMessage()->shouldBe($appended);
    }
}
