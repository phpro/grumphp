<?php

namespace spec\GrumPHP\Locator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Process\ExecutableFinder;

class ExternalCommandSpec extends ObjectBehavior
{
    function let(ExecutableFinder $executableFinder)
    {
        $this->beConstructedWith('bin', $executableFinder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Locator\ExternalCommand');
    }

    function it_throws_exception_when_external_command_is_not_found(ExecutableFinder $executableFinder)
    {
        $executableFinder->find('test', null, array('bin'))->willReturn(false);
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringLocate('test');
    }

    function it_locates_external_commands(ExecutableFinder $executableFinder)
    {
        $executableFinder->find('test', null, array('bin'))->willReturn('bin/test');
        $this->locate('test')->shouldEqual('bin/test');
    }
}
