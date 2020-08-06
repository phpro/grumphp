<?php

namespace spec\GrumPHP\Exception;

use GrumPHP\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;
use GrumPHP\Exception\ProcessException;

class ProcessExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProcessException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(RuntimeException::class);
    }

    public function it_can_be_created_when_tmp_file_cannot_be_created(): void
    {
        $this->beConstructedThrough('tmpFileCouldNotBeCreated', []);

        $this->shouldHaveType(ProcessException::class);
    }
}
