<?php

namespace spec\GrumPHP\Linter;

use GrumPHP\Linter\LintError;
use PhpSpec\ObjectBehavior;

class LintErrorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(LintError::TYPE_ERROR, 'error', 'file.txt', 1);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LintError::class);
    }

    public function it_has_an_error_type()
    {
        $this->getType()->shouldBe(LintError::TYPE_ERROR);
    }

    public function it_has_an_error_message()
    {
        $this->getError()->shouldBe('error');
    }

    public function it_has_a_file()
    {
        $this->getFile()->shouldBe('file.txt');
    }

    public function it_has_a_line_number()
    {
        $this->getLine()->shouldBe(1);
    }

    public function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] file.txt: error on line 1');
    }
}
