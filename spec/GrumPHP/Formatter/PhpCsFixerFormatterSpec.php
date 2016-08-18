<?php

namespace spec\GrumPHP\Formatter;

use GrumPHP\Formatter\PhpCsFixerFormatter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

/**
 * @mixin PhpCsFixerFormatter
 */
class PhpCsFixerFormatterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Formatter\PhpCsFixerFormatter');
    }

    function it_is_a_process_formatter()
    {
        $this->shouldHaveType('GrumPHP\Formatter\ProcessFormatterInterface');
    }

    function it_handles_command_exceptions(Process $process)
    {
        $process->getOutput()->willReturn('');
        $process->getErrorOutput()->willReturn('stderr');
        $this->format($process)->shouldReturn('stderr');
    }

    function it_handles_invalid_json(Process $process)
    {
        $process->getOutput()->willReturn('invalid');
        $this->format($process)->shouldReturn('invalid');
    }

    function it_handles_invalid_file_formats(Process $process)
    {
        $json = $this->parseJson(array('invalid'));
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldStartWith('Invalid file: ');
    }

    function it_formats_php_cs_fixer_json_output_for_single_file(Process $process)
    {
        $json = $this->parseJson(array(
            array('name' => 'name1', ),
        ));
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('1) name1');
    }

    function it_formats_php_cs_fixer_json_output_for_multiple_files(Process $process)
    {
        $json = $this->parseJson(array(
            array('name' => 'name1', ),
            array('name' => 'name2', 'appliedFixers' => array('fixer1', 'fixer2')),
        ));
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('1) name1' . PHP_EOL . '2) name2 (fixer1,fixer2)');
    }

    function it_should_be_possible_to_reset_the_counter(Process $process)
    {
        $json = $this->parseJson(array(array('name' => 'name1')));
        $process->getOutput()->willReturn($json);

        $this->format($process)->shouldBe('1) name1');
        $this->resetCounter();
        $this->format($process)->shouldBe('1) name1');
    }

    function it_formats_suggestions(Process $process)
    {
        $dryRun = ProcessUtils::escapeArgument('--dry-run');
        $formatJson = ProcessUtils::escapeArgument('--format=json');

        $command = sprintf('phpcsfixer %s %s .', $dryRun, $formatJson);

        $process->getCommandLine()->willReturn($command);
        $this->formatSuggestion($process)->shouldReturn('phpcsfixer .');
    }

    function it_formats_the_error_message()
    {
        $this->formatErrorMessage(array('message1'), array('message2'))->shouldBeString();
    }

    /**
     * @param $files
     *
     * @return string
     */
    private function parseJson(array $files)
    {
        return json_encode(array('files' => $files));
    }
}
