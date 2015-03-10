<?php

namespace spec\GrumPHP\Composer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GrumPHPPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Composer\GrumPHPPlugin');
    }

    function it_is_a_composer_plugin()
    {
        $this->shouldHaveType('Composer\Plugin\PluginInterface');
    }

    function it_is_a_composer_event_subscriber()
    {
        $this->shouldHaveType('Composer\EventDispatcher\EventSubscriberInterface');
    }
}
