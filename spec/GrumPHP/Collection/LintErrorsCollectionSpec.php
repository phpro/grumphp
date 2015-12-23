<?php

namespace spec\GrumPHP\Collection;

use GrumPHP\Linter\LintError;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LintErrorsCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(array(
            new LintError(LintError::TYPE_ERROR, 'error', 'file.txt', 1),
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Collection\LintErrorsCollection');
    }

    function it_is_an_array_collection()
    {
        $this->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
    }

    function it_should_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] file.txt: error on line 1');
    }
}
