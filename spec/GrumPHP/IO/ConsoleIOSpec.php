<?php

namespace spec\GrumPHP\IO;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleIOSpec extends ObjectBehavior
{
    function let(InputInterface $input, OutputInterface $output)
    {
        $this->beConstructedWith($input, $output);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\IO\ConsoleIO');
    }

    function it_should_be_a_IO()
    {
        $this->shouldImplement('GrumPHP\IO\IOInterface');
    }

    function it_should_know_if_the_input_is_interactive_modus(InputInterface $input)
    {
        $input->isInteractive()->willReturn(true);
        $this->isInteractive()->shouldBe(true);
    }

    function it_should_know_if_the_output_is_verbose(OutputInterface $output)
    {
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_VERBOSE);
        $this->isVerbose()->shouldBe(true);
    }

    function it_should_know_if_the_output_is_very_verbose(OutputInterface $output)
    {
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $this->isVeryVerbose()->shouldBe(true);
    }

    function it_should_know_if_the_output_is_debug(OutputInterface $output)
    {
        $output->getVerbosity()->willReturn(OutputInterface::VERBOSITY_DEBUG);
        $this->isDebug()->shouldBe(true);
    }

    function it_should_know_if_the_output_is_decorated(OutputInterface $output)
    {
        $output->isDecorated()->willReturn(true);
        $this->isDecorated()->shouldBe(true);
    }

    function it_should_write_messages(OutputInterface $output)
    {
        $output->write('test', true)->shouldBeCalled();
        $this->write('test');
    }

    function it_should_write_error_messages(OutputInterface $output)
    {
        $output->write('test', true)->shouldBeCalled();
        $this->writeError('test');
    }

    function it_should_write_error_messages_to_stderr(ConsoleOutput $cliOutput, OutputInterface $output, InputInterface $input)
    {
        $this->beConstructedWith($input, $cliOutput);
        $cliOutput->getErrorOutput()->willReturn($output);

        $output->write('test', true)->shouldBeCalled();
        $this->writeError('test');
    }
}
