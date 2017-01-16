<?php

namespace spec\GrumPHP\Exception;

use GrumPHP\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;
use GrumPHP\Exception\InvalidArgumentException;

class InvalidArgumentExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InvalidArgumentException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(RuntimeException::class);
    }
}
