<?php

namespace spec\GrumPHP\Configuration\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Configuration\Model\ParallelConfig;

class ParallelConfigSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith($enabled = true, $maxSize = 10);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ParallelConfig::class);
    }

    public function it_is_enabled(): void
    {
        $this->isEnabled()->shouldBe(true);
    }

    public function it_contains_max_size(): void
    {
        $this->getMaxWorkers()->shouldBe(10);
    }

    public function it_can_be_constructed_from_array(): void
    {
        $this->beConstructedThrough('fromArray', [
            [
                'enabled' => true,
                'max_workers' => 10,
            ]
        ]);

        $this->isEnabled()->shouldBe(true);
        $this->getMaxWorkers()->shouldBe(10);
    }
}
