<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Collection\TestSuiteCollection;
use GrumPHP\Exception\InvalidArgumentException;
use GrumPHP\Task\TaskInterface;
use GrumPHP\TestSuite\TestSuiteInterface;
use PhpSpec\ObjectBehavior;

class TestSuiteCollectionSpec extends ObjectBehavior
{
    public function let(TestSuiteInterface $testSuite1, TaskInterface $testSuite2)
    {
        $this->beConstructedWith([
            'suite1' => $testSuite1,
            'suite2' => $testSuite2
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TestSuiteCollection::class);
    }

    function it_should_get_required(TestSuiteInterface $testSuite1)
    {
        $this->getRequired('suite1')->shouldBe($testSuite1);
        $this->shouldThrow(InvalidArgumentException::class)->duringGetRequired('unknown');
    }

    function it_should_get_optional(TestSuiteInterface $testSuite1)
    {
        $this->getOptional('suite1')->shouldBe($testSuite1);
        $this->getOptional('unknown')->shouldBe(null);
    }
}
