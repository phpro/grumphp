<?php

namespace spec\GrumPHP\TestSuite;

use GrumPHP\TestSuite\TestSuiteInterface;
use PhpSpec\ObjectBehavior;
use GrumPHP\TestSuite\TestSuite;

class TestSuiteSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('name', ['task1']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TestSuite::class);
    }

    function it_is_a_testsuite()
    {
        $this->shouldImplement(TestSuiteInterface::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldBe('name');
    }

    function it_has_task_names()
    {
        $this->getTaskNames()->shouldBe(['task1']);
    }
}
