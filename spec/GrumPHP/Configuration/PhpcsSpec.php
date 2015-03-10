<?php

namespace spec\GrumPHP\Configuration;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PhpcsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Configuration\Phpcs');
    }

    function it_is_a_grumphp_configuration()
    {
        $this->shouldHaveType('GrumPHP\Configuration\ConfigurationInterface');
    }

    function it_incorporates_the_base_functionality()
    {
        $this->shouldHaveType('GrumPHP\Configuration\AbstractConfiguration');
    }

    function it_knows_about_the_standard_option()
    {
        $this->setStandard('standard');
        $this->getStandard()->shouldEqual('standard');
    }
}
