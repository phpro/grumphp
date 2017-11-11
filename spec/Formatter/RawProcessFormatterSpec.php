<?php

namespace spec\GrumPHP\Formatter;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Formatter\RawProcessFormatter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;

class RawProcessFormatterSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(RawProcessFormatter::class);
    }

    public function it_is_a_process_formatter()
    {
        $this->shouldHaveType(ProcessFormatterInterface::class);
    }

    public function it_displays_the_full_process_output(Process $process)
    {
        $process->getOutput()->willReturn('stdout');
        $process->getErrorOutput()->willReturn('stderr');
        $this->format($process)->shouldReturn('stdout' . PHP_EOL . 'stderr');
    }

    public function it_displays_stdout_only(Process $process)
    {
        $process->getOutput()->willReturn('stdout');
        $process->getErrorOutput()->willReturn('');
        $this->format($process)->shouldReturn('stdout');
    }

    public function it_displays_stderr_only(Process $process)
    {
        $process->getOutput()->willReturn('');
        $process->getErrorOutput()->willReturn('stderr');
        $this->format($process)->shouldReturn('stderr');
    }
}
