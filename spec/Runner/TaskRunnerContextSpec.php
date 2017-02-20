<?php

namespace spec\GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\TestSuite\TestSuiteInterface;
use PhpSpec\ObjectBehavior;
use GrumPHP\Runner\TaskRunnerContext;

class TaskRunnerContextSpec extends ObjectBehavior
{
    public function let(ContextInterface $context, TestSuiteInterface $testSuite)
    {
        $this->beConstructedWith($context, $testSuite);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TaskRunnerContext::class);
    }

    public function it_has_a_task_context(ContextInterface $context)
    {
        $this->getTaskContext()->shouldBe($context);
    }

    public function it_has_a_test_suite(TestSuiteInterface $testSuite)
    {
        $this->hasTestSuite()->shouldBe(true);
        $this->getTestSuite()->shouldBe($testSuite);
    }

    public function it_has_no_test_suite(ContextInterface $context)
    {
        $this->beConstructedWith($context);
        $this->hasTestSuite()->shouldBe(false);
        $this->getTestSuite()->shouldBe(null);
    }

    public function it_knows_to_skip_the_success_message()
    {
        $this->isSkipSuccessOutput()->shouldBe(false);
        $this->setSkipSuccessOutput(true);
        $this->isSkipSuccessOutput()->shouldBe(true);
    }
}
