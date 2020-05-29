<?php

namespace spec\GrumPHP\Configuration\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Configuration\Model\AsciiConfig;

class AsciiConfigSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(null);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AsciiConfig::class);
    }

    function it_should_return_empty_ascii_location_for_empty_resources()
    {
        $this->beConstructedWith(null);
        $this->fetchResource('success')->shouldReturn(null);
    }

    function it_should_return_empty_ascii_location_for_unknown_resources()
    {
        $this->beConstructedWith(['success' => 'success']);
        $this->fetchResource('dontknow')->shouldReturn(null);
    }

    function it_should_return_the_ascii_location_for_known_resources()
    {
        $this->beConstructedWith(['success' => 'success']);
        $this->fetchResource('success')->shouldReturn('success');
    }

    function it_should_return_the_ascii_location_from_list()
    {
        $this->beConstructedWith(['success' => ['success.txt']]);
        $this->fetchResource('success')->shouldReturn('success.txt');
    }
}
