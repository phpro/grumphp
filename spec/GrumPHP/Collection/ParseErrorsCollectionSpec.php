<?php

namespace spec\GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\ParseError;
use PhpSpec\ObjectBehavior;

class ParseErrorsCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            new ParseError(ParseError::TYPE_ERROR, 'Found "count" function call', 'Ant.php', 58),
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ParseErrorsCollection::class);
    }

    function it_is_an_array_collection()
    {
        $this->shouldHaveType(ArrayCollection::class);
    }

    function it_should_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] Ant.php: Found "count" function call on line 58');
    }
}
