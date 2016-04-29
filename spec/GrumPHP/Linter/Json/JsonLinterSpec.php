<?php

namespace spec\GrumPHP\Linter\Json;

use GrumPHP\Linter\Json\JsonLinter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin JsonLinter
 */
class JsonLinterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Linter\Json\JsonLinter');
    }

    function it_is_a_linter()
    {
        $this->shouldImplement('GrumPHP\Linter\LinterInterface');
    }
}
