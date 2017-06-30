<?php

namespace spec\GrumPHP\Formatter;

use GrumPHP\Formatter\GitBlacklistFormatter;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\IO\ConsoleIO;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;

class GitBlacklistFormatterSpec extends ObjectBehavior
{
    function let(ConsoleIO $consoleIO)
    {
        $consoleIO->isDecorated()->willReturn(true);
        $this->beConstructedWith($consoleIO);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GitBlacklistFormatter::class);
    }

    function it_is_a_process_formatter()
    {
        $this->shouldHaveType(ProcessFormatterInterface::class);
    }

    function it_does_not_displays_the_full_process_output(Process $process)
    {
        $process->getOutput()->willReturn('stdout');
        $process->getErrorOutput()->willReturn('stderr');
        $this->format($process)->shouldReturn("\033[m" . 'stdout');
    }

    function it_displays_stdout_only(Process $process)
    {
        $process->getOutput()->willReturn('stdout');
        $process->getErrorOutput()->willReturn('');
        $this->format($process)->shouldReturn("\033[m" . 'stdout');
    }

    function it_displays_stderr_only(Process $process)
    {
        $process->getOutput()->willReturn('');
        $process->getErrorOutput()->willReturn('stderr');
        $this->format($process)->shouldReturn('stderr');
    }

    function it_displays_stdout_simple(Process $process)
    {
        $process->getOutput()->willReturn('normal/file.php' . "\n" . "8\033[36m:\033[m\033[1;31mvar_dump(\033[m'bla');" . "\n");
        $process->getErrorOutput()->willReturn('');
        $this->format($process)->shouldReturn("\033[mnormal/file.php" . PHP_EOL . "8\033[36m:\033[m\033[1;31mvar_dump(\033[m'bla');");
    }

    function it_displays_stdout_long(Process $process)
    {
        $process->getOutput()->willReturn('normal/file.php' . "\n" . "42\033[36m:\033[mstuff stuff g;if(d.options.debug===!0&&(\033[1;31mvar_dump(\033[m\"Active tab is \"),d.options.debug.href,f=g.split(\"#\")[1],d.options.debug===!0&&\033[1;31mvar_dump(\033[m\"Pushed state \"+g);" . "\n");
        $process->getErrorOutput()->willReturn('');
        $this->format($process)->shouldReturn(
            "\033[mnormal/file.php" . PHP_EOL
            . "  1\033[36m:\033[m51\033[36m:\033[m ptions.debug===!0&&(\033[1;31mvar_dump(\033[m\"Active tab is \")\033[m" . PHP_EOL
            . "  1\033[36m:\033[m149\033[36m:\033[m options.debug===!0&&\033[1;31mvar_dump(\033[m\"Pushed state \"+g\033[m"
        );
    }

    function it_displays_stdout_special_correctly(Process $process)
    {
        $process->getOutput()->willReturn('normal/file.php' . "\n" . "8\033[36m:\033[m\t\033[1;31mprivate $\033[m' . \$this->callSomeVeryVeryVeryLongMethodName() . ' = null;" . "\n");
        $process->getErrorOutput()->willReturn('');
        $this->format($process)->shouldReturn(
            "\033[mnormal/file.php" . PHP_EOL
            . "  1\033[36m:\033[m20\033[36m:\033[m 8\033[36m:\033[m\t\033[1;31mprivate $\033[m' . \$this->callSo\033[m"
        );
    }
}
