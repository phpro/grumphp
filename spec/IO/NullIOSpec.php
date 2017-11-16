<?php

namespace spec\GrumPHP\IO;

use GrumPHP\IO\IOInterface;
use GrumPHP\IO\NullIO;
use PhpSpec\ObjectBehavior;

class NullIOSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(NullIO::class);
    }

    public function it_should_be_a_IO()
    {
        $this->shouldImplement(IOInterface::class);
    }

    public function it_should_know_if_the_input_is_interactive_modus()
    {
        $this->isInteractive()->shouldBe(false);
    }

    public function it_should_know_if_the_output_is_verbose()
    {
        $this->isVerbose()->shouldBe(false);
    }

    public function it_should_know_if_the_output_is_very_verbose()
    {
        $this->isVeryVerbose()->shouldBe(false);
    }

    public function it_should_know_if_the_output_is_debug()
    {
        $this->isDebug()->shouldBe(false);
    }

    public function it_should_know_if_the_output_is_decorated()
    {
        $this->isDecorated()->shouldBe(false);
    }
}
