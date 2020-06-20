<?php

namespace spec\GrumPHP\Formatter;

use GrumPHP\Formatter\ESLintFormatter;
use GrumPHP\Formatter\ProcessFormatterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;
use GrumPHP\Process\ProcessUtils;

class ESLintFormatterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ESLintFormatter::class);
    }

    function it_is_a_process_formatter()
    {
        $this->shouldHaveType(ProcessFormatterInterface::class);
    }

    function it_handles_command_exceptions(Process $process)
    {
        $process->getOutput()->willReturn('');
        $process->getErrorOutput()->willReturn('stderr');
        $this->format($process)->shouldReturn('stderr');
    }

    function it_formats_the_error_message()
    {
        $this->formatErrorMessage(['message1'], ['message2'])->shouldBeString();
    }
}
