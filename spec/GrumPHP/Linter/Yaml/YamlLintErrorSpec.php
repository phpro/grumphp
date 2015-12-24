<?php

namespace spec\GrumPHP\Linter\Yaml;

use GrumPHP\Linter\LintError;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class YamlLintErrorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(LintError::TYPE_ERROR, 'Full Yaml Parser Exception', 'file.txt', 1, 'snippet');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Linter\Yaml\YamlLintError');
    }

    function it_is_a_lint_error()
    {
        $this->shouldHaveType('GrumPHP\Linter\LintError');
    }


    function it_has_a_snippet()
    {
        $this->getSnippet()->shouldBe('snippet');
    }

    function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] Full Yaml Parser Exception');
    }
}
