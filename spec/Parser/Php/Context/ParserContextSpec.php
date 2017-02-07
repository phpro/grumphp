<?php

namespace spec\GrumPHP\Parser\Php\Context;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\Php\Context\ParserContext;
use PhpSpec\ObjectBehavior;
use SplFileInfo;

class ParserContextSpec extends ObjectBehavior
{
    function let(SplFileInfo $file, ParseErrorsCollection $errors)
    {
        $this->beConstructedWith($file, $errors);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ParserContext::class);
    }

    function it_contains_a_file(SplFileInfo $file)
    {
        $this->getFile()->shouldBe($file);
    }

    function it_contains_parse_errors(ParseErrorsCollection $errors)
    {
        $this->getErrors()->shouldBe($errors);
    }
}
