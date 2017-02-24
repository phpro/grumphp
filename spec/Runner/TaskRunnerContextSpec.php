<?php

namespace spec\GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\TestSuite\TestSuiteInterface;
use PhpSpec\ObjectBehavior;
use GrumPHP\Runner\TaskRunnerContext;

class TaskRunnerContextSpec extends ObjectBehavior
{
    function let(ContextInterface $context, TestSuiteInterface $testSuite)
    {
        $this->beConstructedWith($context, $testSuite);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TaskRunnerContext::class);
    }

    function it_has_a_task_context(ContextInterface $context)
    {
        $this->getTaskContext()->shouldBe($context);
    }

    function it_has_a_test_suite(TestSuiteInterface $testSuite)
    {
        $this->hasTestSuite()->shouldBe(true);
        $this->getTestSuite()->shouldBe($testSuite);
    }

    function it_has_no_test_suite(ContextInterface $context)
    {
        $this->beConstructedWith($context);
        $this->hasTestSuite()->shouldBe(false);
        $this->getTestSuite()->shouldBe(null);
    }

    function it_knows_to_skip_the_success_message()
    {
        $this->skipSuccessOutput()->shouldBe(false);
        $this->setSkipSuccessOutput(true);
        $this->skipSuccessOutput()->shouldBe(true);
    }
}
