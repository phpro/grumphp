<?php

namespace spec\GrumPHP\Parser;

use GrumPHP\Parser\ParseError;
use PhpSpec\ObjectBehavior;

class ParseErrorSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(ParseError::TYPE_ERROR, 'Found "count" function call', 'Behat.php', 59);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ParseError::class);
    }

    public function it_has_an_error_type()
    {
        $this->getType()->shouldBe(ParseError::TYPE_ERROR);
    }

    public function it_has_an_error_message()
    {
        $this->getError()->shouldBe('Found "count" function call');
    }

    public function it_has_a_file()
    {
        $this->getFile()->shouldBe('Behat.php');
    }

    public function it_has_a_line_number()
    {
        $this->getLine()->shouldBe(59);
    }

    public function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] Behat.php: Found "count" function call on line 59');
    }
}
