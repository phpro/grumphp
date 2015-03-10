<?php

namespace spec\GrumPHP\Configuration;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PhpspecSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Configuration\Phpspec');
    }

    function it_is_a_grumphp_configuration()
    {
        $this->shouldHaveType('GrumPHP\Configuration\ConfigurationInterface');
    }

    function it_incorporates_the_base_functionality()
    {
        $this->shouldHaveType('GrumPHP\Configuration\AbstractConfiguration');
    }
}
