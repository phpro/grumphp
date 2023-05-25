<?php

namespace spec\GrumPHP\Exception;

use GrumPHP\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;
use GrumPHP\Exception\TaskConfigResolverException;

class TaskConfigResolverExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TaskConfigResolverException::class);
    }

    public function it_is_a_runtime_exception(): void
    {
        $this->shouldImplement(RuntimeException::class);
    }

    public function it_handles_unknown_tasks(): void
    {
        $this->beConstructedThrough('unknownTask', [$task = 'taskName']);
        $this->getMessage()->shouldContain($task);
    }

    public function it_handles_unknown_class(): void
    {
        $this->beConstructedThrough('unknownClass', [$class = 'SomeClass']);
        $this->getMessage()->shouldContain($class);
    }
}
