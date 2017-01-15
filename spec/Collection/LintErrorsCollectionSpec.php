<?php

namespace spec\GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\LintError;
use PhpSpec\ObjectBehavior;

class LintErrorsCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            new LintError(LintError::TYPE_ERROR, 'error', 'file.txt', 1),
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LintErrorsCollection::class);
    }

    function it_is_an_array_collection()
    {
        $this->shouldHaveType(ArrayCollection::class);
    }

    function it_should_be_parsed_as_string()
    {
        $this->__toString()->shouldBe('[ERROR] file.txt: error on line 1');
    }
}
