<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\ParseError;
use PhpSpec\ObjectBehavior;

/**
 * Class ParseErrorsCollectionSpec
 *
 * @package spec\GrumPHP\Collection
 * @mixin ParseErrorsCollection
 */
class ParseErrorsCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(array(
            new ParseError(ParseError::TYPE_ERROR, 'Found "count" function call', 'Ant.php', 58),
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Collection\ParseErrorsCollection');
    }

    function it_is_an_array_collection()
    {
        $this->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
    }

    function it_should_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] Ant.php: Found "count" function call on line 58');
    }
}
