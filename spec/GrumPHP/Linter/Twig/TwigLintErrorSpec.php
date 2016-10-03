<?php

namespace spec\GrumPHP\Linter\Twig;

use GrumPHP\Linter\Twig\TwigLintError;
use GrumPHP\Linter\LintError;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin TwigLintError
 */
class TwigLintErrorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(LintError::TYPE_ERROR, 'Full Twig Parser Exception', 'file.twig', 0);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Linter\Twig\TwigLintError');
    }

    function it_is_a_lint_error()
    {
        $this->shouldHaveType('GrumPHP\Linter\LintError');
    }

    function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] file.twig: Full Twig Parser Exception');
    }
}
