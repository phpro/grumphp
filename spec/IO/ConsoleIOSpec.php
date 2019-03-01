<?php

namespace spec\GrumPHP\IO;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\IO\ConsoleIO;
use GrumPHP\IO\IOInterface;
use PhpSpec\ObjectBehavior;
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
        $this->shouldHaveType(ConsoleIO::class);
    }

    function it_should_be_a_IO()
    {
        $this->shouldImplement(IOInterface::class);
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
        $output->write(['test'], true)->shouldBeCalled();
        $this->write(['test']);
    }

    function it_should_write_error_messages(OutputInterface $output)
    {
        $output->write(['test'], true)->shouldBeCalled();
        $this->writeError(['test']);
    }

    function it_should_write_error_messages_to_stderr(ConsoleOutput $cliOutput, OutputInterface $output, InputInterface $input)
    {
        $this->beConstructedWith($input, $cliOutput);
        $cliOutput->getErrorOutput()->willReturn($output);

        $output->write(['test'], true)->shouldBeCalled();
        $this->writeError(['test']);
    }

    function it_reads_command_input()
    {
        $handle = $this->mockHandle('input');
        $this->readCommandInput($handle)->shouldBe('input');
    }

    function it_reads_actual_diffs_from_input()
    {
        $diff = <<<EOD
diff --git a/src/test.php b/src/test.php
index 372bf10b74013301cfb4bf0e8007d208bb813363..d95f50da4a02d3d203bda1f3cb94e29d4f0ef481 100644
--- a/src/test.php
+++ b/src/test.php
@@ -2,3 +2,4 @@


 'something';
+'ok';

EOD;

        $handle = $this->mockHandle($diff);
        $this->readCommandInput($handle)->shouldBe($diff);
    }

    function it_knows_empty_command_input()
    {
        $handle = $this->mockHandle(" \r\n\t\f ");
        $this->readCommandInput($handle)->shouldBe('');
    }

    function it_only_reads_valid_command_input_resource_streams()
    {
        $this->shouldThrow(RuntimeException::class)->duringReadCommandInput('string');
    }

    private function mockHandle($content)
    {
        $handle = fopen('php://memory', 'a');
        fwrite($handle, $content);
        rewind($handle);

        return $handle;
    }
}
