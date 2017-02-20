<?php

namespace spec\GrumPHP\Linter\Json;

use GrumPHP\Linter\Json\JsonLintError;
use GrumPHP\Linter\LintError;
use PhpSpec\ObjectBehavior;

class JsonLintErrorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(LintError::TYPE_ERROR, 'Full Json Parser Exception', 'file.json', 0);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(JsonLintError::class);
    }

    public function it_is_a_lint_error()
    {
        $this->shouldHaveType(LintError::class);
    }

    public function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] file.json: Full Json Parser Exception');
    }
}
