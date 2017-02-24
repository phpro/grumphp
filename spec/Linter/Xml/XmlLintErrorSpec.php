<?php

namespace spec\GrumPHP\Linter\Xml;

use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Xml\XmlLintError;
use PhpSpec\ObjectBehavior;

class XmlLintErrorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(LintError::TYPE_ERROR, 0, 'error', 'file.txt', 1, 1);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(XmlLintError::class);
    }

    public function it_is_a_lint_error()
    {
        $this->shouldHaveType(LintError::class);
    }

    public function it_has_an_error_code()
    {
        $this->getCode()->shouldBe(0);
    }

    public function it_has_a_column_number()
    {
        $this->getColumn()->shouldBe(1);
    }

    public function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] file.txt: error (0) on line 1,1');
    }
}
