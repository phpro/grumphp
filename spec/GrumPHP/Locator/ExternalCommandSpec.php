<?php

namespace spec\GrumPHP\Locator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class ExternalCommandSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem)
    {
        $this->beConstructedWith('bin', $filesystem);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Locator\ExternalCommand');
    }

    function it_is_a_grumphp_locator()
    {
        $this->shouldHaveType('GrumPHP\Locator\LocatorInterface');
    }

    function it_throws_exception_when_external_command_is_not_found(Filesystem $filesystem)
    {
        $filesystem->exists('bin/test')->willReturn(false);
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringLocate('test');
    }

    function it_locates_external_commands(Filesystem $filesystem)
    {
        $filesystem->exists('bin/test')->willReturn(true);
        $this->locate('test')->shouldEqual('bin/test');
    }
}
