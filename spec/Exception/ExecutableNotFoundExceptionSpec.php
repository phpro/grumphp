<?php

namespace spec\GrumPHP\Exception;

use GrumPHP\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;
use GrumPHP\Exception\ExecutableNotFoundException;

class ExecutableNotFoundExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ExecutableNotFoundException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(RuntimeException::class);
    }

    public function it_can_be_created_for_command(): void
    {
        $this->beConstructedThrough('forCommand', ['theCommand']);

        $this->shouldHaveType(ExecutableNotFoundException::class);
        $this->getMessage()->shouldContain('theCommand');
    }
}
