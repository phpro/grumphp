<?php

namespace spec\GrumPHP\Linter\Yaml;

use GrumPHP\Linter\Yaml\YamlLinter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin YamlLinter
 */
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
