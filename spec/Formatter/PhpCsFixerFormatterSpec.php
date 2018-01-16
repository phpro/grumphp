<?php

namespace spec\GrumPHP\Formatter;

use GrumPHP\Formatter\PhpCsFixerFormatter;
use GrumPHP\Formatter\ProcessFormatterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;
use GrumPHP\Process\ProcessUtils;

class PhpCsFixerFormatterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PhpCsFixerFormatter::class);
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

    function it_handles_invalid_json(Process $process)
    {
        $process->getOutput()->willReturn('invalid');
        $this->format($process)->shouldReturn('invalid');
    }

    function it_handles_invalid_file_formats(Process $process)
    {
        $json = $this->parseJson(['invalid']);
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldStartWith('Invalid file: ');
    }

    function it_formats_phpcsfixer_json_output_for_single_file(Process $process)
    {
        $json = $this->parseJson([
            ['name' => 'name1',],
        ]);
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('1) name1');
    }

    function it_formats_phpcsfixer_json_output_for_multiple_files(Process $process)
    {
        $json = $this->parseJson([
            ['name' => 'name1',],
            ['name' => 'name2', 'appliedFixers' => ['fixer1', 'fixer2']],
        ]);
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('1) name1' . PHP_EOL . '2) name2 (fixer1, fixer2)');
    }

    function it_formats_phpcsfixer_json_output_for_diff(Process $process)
    {
        $json = $this->parseJson([
            ['name' => 'name1', 'diff' => 'diff1'],
        ]);
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('1) name1' . PHP_EOL . PHP_EOL . 'diff1');
    }

    function it_should_be_possible_to_reset_the_counter(Process $process)
    {
        $json = $this->parseJson([['name' => 'name1']]);
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
        $this->formatErrorMessage(['message1'], ['message2'])->shouldBeString();
    }

    /**
     * @param $files
     *
     * @return string
     */
    private function parseJson(array $files)
    {
        return json_encode(['files' => $files]);
    }
}
