<?php

namespace spec\GrumPHP\Finder;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FinderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Finder\Finder');
    }

    function it_should_create_symfony_finder_based_on_file_list()
    {
        $result = $this->create(array('file1'));
        $result->shouldBeAnInstanceOf('Symfony\Component\Finder\Finder');
        $result->count()->shouldBe(1);
    }
}
