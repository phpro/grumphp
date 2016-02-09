<?php

namespace spec\GrumPHP\IO;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NullIOSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\IO\NullIO');
    }


    function it_should_be_a_IO()
    {
        $this->shouldImplement('GrumPHP\IO\IOInterface');
    }

    function it_should_know_if_the_input_is_interactive_modus()
    {
        $this->isInteractive()->shouldBe(false);
    }

    function it_should_know_if_the_output_is_verbose()
    {
        $this->isVerbose()->shouldBe(false);
    }

    function it_should_know_if_the_output_is_very_verbose()
    {
        $this->isVeryVerbose()->shouldBe(false);
    }

    function it_should_know_if_the_output_is_debug()
    {
        $this->isDebug()->shouldBe(false);
    }

    function it_should_know_if_the_output_is_decorated()
    {
        $this->isDecorated()->shouldBe(false);
    }
}
