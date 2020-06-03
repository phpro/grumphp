<?php

namespace spec\GrumPHP\Exception;

use GrumPHP\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;
use GrumPHP\Exception\DeprecatedException;

class DeprecatedExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DeprecatedException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(RuntimeException::class);
    }

    public function it_can_be_created_from_direct_param_config(): void
    {
        $this->beConstructedThrough('directParameterConfiguration', [$key = 'configKey']);
        $this->shouldHaveType(DeprecatedException::class);
        $this->getMessage()->shouldContain($key);
    }
}
