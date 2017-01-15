<?php

namespace spec\GrumPHP\Locator;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Locator\ExternalCommand;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\ExecutableFinder;

class ExternalCommandSpec extends ObjectBehavior
{
    function let(ExecutableFinder $executableFinder)
    {
        $this->beConstructedWith('bin', $executableFinder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ExternalCommand::class);
    }

    function it_throws_exception_when_external_command_is_not_found(ExecutableFinder $executableFinder)
    {
        $executableFinder->find('test', null, ['bin'])->willReturn(false);
        $this->shouldThrow(RuntimeException::class)->duringLocate('test');
    }

    function it_locates_external_commands(ExecutableFinder $executableFinder)
    {
        $executableFinder->find('test', null, ['bin'])->willReturn('bin/test');
        $this->locate('test')->shouldEqual('bin/test');
    }
}
