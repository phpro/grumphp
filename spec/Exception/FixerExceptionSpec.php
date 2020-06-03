<?php

namespace spec\GrumPHP\Exception;

use GrumPHP\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;
use GrumPHP\Exception\FixerException;
use Symfony\Component\Process\Process;

class FixerExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FixerException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(RuntimeException::class);
    }

    public function it_can_be_created_from_process(Process $process): void
    {
        $this->beConstructedThrough('fromProcess', [$process]);

        $process->getCommandLine()->willReturn($command = 'cli-command');
        $process->getOutput()->willReturn($stdout = 'output');
        $process->getErrorOutput()->willReturn($stderr = 'error-output');

        $this->shouldHaveType(FixerException::class);
        $this->getMessage()->shouldContain($command);
        $this->getMessage()->shouldContain($stdout);
        $this->getMessage()->shouldContain($stderr);
    }
}
