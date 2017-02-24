<?php

namespace spec\GrumPHP\Formatter;

use GrumPHP\Formatter\PhpCsFixerFormatter;
use GrumPHP\Formatter\ProcessFormatterInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

class PhpCsFixerFormatterSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(PhpCsFixerFormatter::class);
    }

    public function it_is_a_process_formatter()
    {
        $this->shouldHaveType(ProcessFormatterInterface::class);
    }

    public function it_handles_command_exceptions(Process $process)
    {
        $process->getOutput()->willReturn('');
        $process->getErrorOutput()->willReturn('stderr');
        $this->format($process)->shouldReturn('stderr');
    }

    public function it_handles_invalid_json(Process $process)
    {
        $process->getOutput()->willReturn('invalid');
        $this->format($process)->shouldReturn('invalid');
    }

    public function it_handles_invalid_file_formats(Process $process)
    {
        $json = $this->parseJson(['invalid']);
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldStartWith('Invalid file: ');
    }

    public function it_formats_php_cs_fixer_json_output_for_single_file(Process $process)
    {
        $json = $this->parseJson([
            ['name' => 'name1',],
        ]);
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('1) name1');
    }

    public function it_formats_php_cs_fixer_json_output_for_multiple_files(Process $process)
    {
        $json = $this->parseJson([
            ['name' => 'name1',],
            ['name' => 'name2', 'appliedFixers' => ['fixer1', 'fixer2']],
        ]);
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('1) name1' . PHP_EOL . '2) name2 (fixer1,fixer2)');
    }

    public function it_formats_php_cs_fixer_json_output_for_diff(Process $process)
    {
        $json = $this->parseJson([
            ['name' => 'name1', 'diff' => 'diff1'],
        ]);
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('1) name1' . PHP_EOL . PHP_EOL . 'diff1');
    }

    public function it_should_be_possible_to_reset_the_counter(Process $process)
    {
        $json = $this->parseJson([['name' => 'name1']]);
        $process->getOutput()->willReturn($json);

        $this->format($process)->shouldBe('1) name1');
        $this->resetCounter();
        $this->format($process)->shouldBe('1) name1');
    }

    public function it_formats_suggestions(Process $process)
    {
        $dryRun = ProcessUtils::escapeArgument('--dry-run');
        $formatJson = ProcessUtils::escapeArgument('--format=json');

        $command = sprintf('phpcsfixer %s %s .', $dryRun, $formatJson);

        $process->getCommandLine()->willReturn($command);
        $this->formatSuggestion($process)->shouldReturn('phpcsfixer .');
    }

    public function it_formats_the_error_message()
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
