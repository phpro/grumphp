<?php

namespace spec\GrumPHP\Composer;

use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Plugin\PluginInterface;
use GrumPHP\Composer\GrumPHPPlugin;
use PhpSpec\ObjectBehavior;

class GrumPHPPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GrumPHPPlugin::class);
    }

    function it_is_a_composer_plugin()
    {
        $this->shouldHaveType(PluginInterface::class);
    }

    function it_is_a_composer_event_subscriber()
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }
}
