<?php

namespace spec\GrumPHP\Linter;

use GrumPHP\Linter\LintError;
use PhpSpec\ObjectBehavior;

class LintErrorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(LintError::TYPE_ERROR, 'error', 'file.txt', 1);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LintError::class);
    }

    function it_has_an_error_type()
    {
        $this->getType()->shouldBe(LintError::TYPE_ERROR);
    }

    function it_has_an_error_message()
    {
        $this->getError()->shouldBe('error');
    }

    function it_has_a_file()
    {
        $this->getFile()->shouldBe('file.txt');
    }

    function it_has_a_line_number()
    {
        $this->getLine()->shouldBe(1);
    }

    function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] file.txt: error on line 1');
    }
}
