<?php

namespace spec\GrumPHP\Configuration\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Configuration\Model\RunnerConfig;

class RunnerConfigSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            $stopOnFailure = true,
            $hideCircumventionTip = true,
            $additionalInfo = 'additional info'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RunnerConfig::class);
    }

    public function it_can_stop_on_failure(): void
    {
        $this->stopOnFailure()->shouldBe(true);
    }

    public function it_can_hide_circumvention_tip(): void
    {
        $this->hideCircumventionTip()->shouldBe(true);
    }

    public function it_has_additional_info(): void
    {
        $this->getAdditionalInfo()->shouldBe('additional info');
    }
}
