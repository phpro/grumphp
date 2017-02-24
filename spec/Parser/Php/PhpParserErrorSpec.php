<?php

namespace spec\GrumPHP\Parser\Php;

use GrumPHP\Parser\ParseError;
use GrumPHP\Parser\Php\PhpParserError;
use PhpParser\Error;
use PhpSpec\ObjectBehavior;

class PhpParserErrorSpec extends ObjectBehavior
{
    public function let()
    {
        $exception = new Error('syntax error', ['startLine' => 61]);
        $this->beConstructedThrough('fromParseException', [$exception, 'JsonLint.php']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PhpParserError::class);
    }

    public function it_has_an_error_type()
    {
        $this->getType()->shouldBe(ParseError::TYPE_FATAL);
    }

    public function it_has_an_error_message()
    {
        $this->getError()->shouldBe('syntax error');
    }

    public function it_has_a_file()
    {
        $this->getFile()->shouldBe('JsonLint.php');
    }

    public function it_has_a_line_number()
    {
        $this->getLine()->shouldBe(61);
    }

    public function it_can_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[FATAL] JsonLint.php: syntax error on line 61');
    }
}
