<?php

namespace spec\GrumPHP\Configuration\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Configuration\Model\GitStashConfig;

class GitStashConfigSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(true);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GitStashConfig::class);
    }

    public function it_ignores_unstashed_changes(): void
    {
        $this->ignoreUnstagedChanges()->shouldBe(true);
    }
}
