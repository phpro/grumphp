<?php

namespace spec\GrumPHP\Linter\Json;

use GrumPHP\Linter\Json\JsonLinter;
use GrumPHP\Linter\LinterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin JsonLinter
 */
class JsonLinterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(JsonLinter::class);
    }

    function it_is_a_linter()
    {
        $this->shouldImplement(LinterInterface::class);
    }
}
