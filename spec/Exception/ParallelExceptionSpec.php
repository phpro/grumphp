<?php

namespace spec\GrumPHP\Exception;

use GrumPHP\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;
use GrumPHP\Exception\ParallelException;

class ParallelExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ParallelException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(RuntimeException::class);
    }

    public function it_can_be_created_from_other_exception(): void
    {
        $other = new \Exception('other', 100);
        $this->beConstructedThrough('fromThrowable', [$other]);
        $this->shouldHaveType(ParallelException::class);
        $this->getCode()->shouldBe($other->getCode());
        $this->getMessage()->shouldBe($other->getMessage());
        $this->getPrevious()->shouldBe($other);
    }

    public function it_can_be_created_from_other_exception_in_verbose_way(): void
    {
        $other = new \Exception('other', 100);
        $this->beConstructedThrough('fromVerboseThrowable', [$other]);
        $this->shouldHaveType(ParallelException::class);
        $this->getCode()->shouldBe($other->getCode());
        $this->getMessage()->shouldContain($other->getMessage());
        $this->getMessage()->shouldContain($other->getTraceAsString());
        $this->getPrevious()->shouldBe($other);
    }
}
