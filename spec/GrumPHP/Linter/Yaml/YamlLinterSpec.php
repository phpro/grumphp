<?php

namespace spec\GrumPHP\Linter\Yaml;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class YamlLinterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Linter\Yaml\YamlLinter');
    }

    function it_is_a_linter()
    {
        $this->shouldImplement('GrumPHP\Linter\LinterInterface');
    }
}
