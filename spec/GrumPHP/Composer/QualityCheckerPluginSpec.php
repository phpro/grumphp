<?php

namespace spec\GrumPHP\Composer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class QualityCheckerPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Composer\QualityCheckerPlugin');
    }

    function it_is_a_composer_plugin()
    {
        $this->shouldHaveType('Composer\Plugin\PluginInterface');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Composer\EventDispatcher\EventSubscriberInterface');
    }
}
