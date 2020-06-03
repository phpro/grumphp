<?php

namespace spec\GrumPHP\Configuration\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Configuration\Model\FixerConfig;

class FixerConfigSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(true, true);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FixerConfig::class);
    }

    public function it_is_enabled(): void
    {
        $this->isEnabled()->shouldBe(true);
    }

    public function it_fixes_by_default(): void
    {
        $this->fixByDefault()->shouldBe(true);
    }

    public function it_can_be_constructed_through_array(): void
    {
        $this->beConstructedThrough('fromArray', [
            [
                'enabled' => true,
                'fix_by_default' => true,
            ]
        ]);

        $this->isEnabled()->shouldBe(true);
        $this->fixByDefault()->shouldBe(true);
    }
}
