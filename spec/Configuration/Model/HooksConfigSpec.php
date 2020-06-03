<?php

namespace spec\GrumPHP\Configuration\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Configuration\Model\HooksConfig;

class HooksConfigSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            $dir = 'hookDir',
            $preset = 'preset',
            ['variable' => true]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HooksConfig::class);
    }

    public function it_contains_dir(): void
    {
        $this->getDir()->shouldBe('hookDir');
    }

    public function it_contains_preset(): void
    {
        $this->getPreset()->shouldBe('preset');
    }

    public function it_contains_variables(): void
    {
        $this->getVariables()->shouldBe(['variable' => true]);
    }
}
