<?php

namespace spec\GrumPHP\Runner;

use GrumPHP\Collection\TasksCollection;
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

    function it_has_no_tasks()
    {
        $this->hasTaskNames()->shouldBe(false);
        $this->getTaskNames()->shouldBe([]);
    }

    function it_has_tasks(ContextInterface $context, TestSuiteInterface $testSuite)
    {
        $tasks = ["task_1"];
        $this->beConstructedWith($context, $testSuite, $tasks);
        $this->hasTaskNames()->shouldBe(true);
        $this->getTaskNames()->shouldBe($tasks);
    }

    function it_knows_to_skip_the_success_message()
    {
        $this->skipSuccessOutput()->shouldBe(false);
        $new = $this->withSkippedSuccessOutput(true);
        $new->shouldNotBe($this);
        $new->skipSuccessOutput()->shouldBe(true);
    }

    public function it_can_add_tasks_to_run(): void
    {
        $new = $this->withTasks($tasks = new TasksCollection());
        $this->shouldNotBe($new);
        $new->getTasks()->shouldBe($tasks);
        $new->getTaskContext()->shouldBe($this->getTaskContext());
    }
}
