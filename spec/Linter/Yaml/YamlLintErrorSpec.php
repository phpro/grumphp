<?php

namespace spec\GrumPHP\Linter\Yaml;

use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Yaml\YamlLintError;
use PhpSpec\ObjectBehavior;

class YamlLintErrorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(LintError::TYPE_ERROR, 'Full Yaml Parser Exception', 'file.txt', 1, 'snippet');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(YamlLintError::class);
    }

    public function it_is_a_lint_error()
    {
        $this->shouldHaveType(LintError::class);
    }

    public function it_has_a_snippet()
    {
        $this->getSnippet()->shouldBe('snippet');
    }

    public function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] Full Yaml Parser Exception');
    }
}
