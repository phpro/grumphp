<?php

namespace spec\GrumPHP\Linter\Xml;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class XmlLinterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Linter\Xml\XmlLinter');
    }

    function it_is_a_linter()
    {
        $this->shouldImplement('GrumPHP\Linter\LinterInterface');
    }
}
