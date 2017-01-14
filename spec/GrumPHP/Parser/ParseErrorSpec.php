<?php

namespace spec\GrumPHP\Parser;

use GrumPHP\Parser\ParseError;
use PhpSpec\ObjectBehavior;

class ParseErrorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(ParseError::TYPE_ERROR, 'Found "count" function call', 'Behat.php', 59);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ParseError::class);
    }

    function it_has_an_error_type()
    {
        $this->getType()->shouldBe(ParseError::TYPE_ERROR);
    }

    function it_has_an_error_message()
    {
        $this->getError()->shouldBe('Found "count" function call');
    }

    function it_has_a_file()
    {
        $this->getFile()->shouldBe('Behat.php');
    }

    function it_has_a_line_number()
    {
        $this->getLine()->shouldBe(59);
    }

    function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] Behat.php: Found "count" function call on line 59');
    }
}
