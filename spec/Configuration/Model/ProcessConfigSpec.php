<?php

namespace spec\GrumPHP\Configuration\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Configuration\Model\ProcessConfig;

class ProcessConfigSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith($timeout = 10.0);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProcessConfig::class);
    }

    public function it_contains_timeout(): void
    {
        $this->getTimeout()->shouldBe(10.0);
    }
}
