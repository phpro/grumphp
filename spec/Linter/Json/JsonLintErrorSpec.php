<?php

namespace spec\GrumPHP\Linter\Json;

use GrumPHP\Linter\Json\JsonLintError;
use GrumPHP\Linter\LintError;
use PhpSpec\ObjectBehavior;

class JsonLintErrorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(LintError::TYPE_ERROR, 'Full Json Parser Exception', 'file.json', 0);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JsonLintError::class);
    }

    function it_is_a_lint_error()
    {
        $this->shouldHaveType(LintError::class);
    }

    function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] file.json: Full Json Parser Exception');
    }
}
