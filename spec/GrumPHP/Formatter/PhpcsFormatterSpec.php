<?php

namespace spec\GrumPHP\Formatter;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Formatter\PhpcsFormatter;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;

class PhpcsFormatterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PhpcsFormatter::class);
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

    function it_formats_phpcs_json_output_for_single_file(Process $process, ProcessBuilder $processBuilder)
    {
        $json = $this->parseJson([
            '/var/www/Classes/Command/CacheCommandController.php' => ['messages' => [['fixable' => true],],],
        ]);

        $arguments = new ProcessArgumentsCollection();
        $process->getOutput()->willReturn($this->getExampleData() . $json);
        $this->format($process)->shouldBe($this->getExampleData());

        $this->getSuggestedFilesFromJson(json_decode($json, true))->shouldBe(['/var/www/Classes/Command/CacheCommandController.php']);

        $processBuilder->buildProcess($arguments)->willReturn($process);
        $process->getCommandLine()->willReturn("'phpcbf' '--standard=PSR2' '--ignore=spec/*Spec.php,test/*.php' '/var/www/Classes/Command/CacheCommandController.php'");
        $this->formatErrorMessage($arguments, $processBuilder)
            ->shouldBe(sprintf(
                '%sYou can fix some errors by running following command:%s',
                PHP_EOL . PHP_EOL,
                PHP_EOL . "'phpcbf' '--standard=PSR2' '--ignore=spec/*Spec.php,test/*.php' '/var/www/Classes/Command/CacheCommandController.php'"
            ));
    }

    function it_formats_phpcs_json_output_for_multiple_files(Process $process, ProcessBuilder $processBuilder)
    {
        $json = $this->parseJson([
            '/var/www/Classes/Command/CacheCommandController.php' => ['messages' => [['fixable' => true],],],
            '/var/www/Classes/Command/DebugCommandController.php' => ['messages' => [['fixable' => false],],],
        ]);

        $arguments = new ProcessArgumentsCollection(['phpcbf']);
        $process->getOutput()->willReturn($this->getExampleData() . $json);
        $this->format($process)->shouldBe($this->getExampleData());

        $this->getSuggestedFilesFromJson(json_decode($json, true))->shouldBe(['/var/www/Classes/Command/CacheCommandController.php']);

        $processBuilder->buildProcess($arguments)->willReturn($process);
        $process->getCommandLine()->willReturn("'phpcbf' '--standard=PSR2' '--ignore=spec/*Spec.php,test/*.php' '/var/www/Classes/Command/CacheCommandController.php'");
        $this->formatErrorMessage($arguments, $processBuilder)
            ->shouldBe(sprintf(
                '%sYou can fix some errors by running following command:%s',
                PHP_EOL . PHP_EOL,
                PHP_EOL . "'phpcbf' '--standard=PSR2' '--ignore=spec/*Spec.php,test/*.php' '/var/www/Classes/Command/CacheCommandController.php'"
            ));
    }

    /**
     * @param $files
     *
     * @return string
     */
    private function parseJson(array $files)
    {
        $fixable = 0;
        foreach ($files as $file) {
            foreach ($file['messages'] as $message) {
                if ($message['fixable']) {
                    $fixable++;
                    break;
                }
            }
        }
        return PHP_EOL . json_encode([
            'totals' => [
                'fixable' => $fixable
            ],
            'files' => $files
        ]);
    }

    private function getExampleData()
    {
        return <<<EOD
FILE: /var/www/Classes/Command/CacheCommandController.php
----------------------------------------------------------------------
FOUND 4 ERRORS AFFECTING 3 LINES
----------------------------------------------------------------------
 28 | ERROR | [x] Opening brace of a class must be on the line after
    |       |     the definition
 36 | ERROR | [x] Opening brace should be on a new line
 49 | ERROR | [x] Expected 1 newline at end of file; 0 found
 49 | ERROR | [x] The closing brace for the class must go on the next
    |       |     line after the body
----------------------------------------------------------------------
PHPCBF CAN FIX THE 4 MARKED SNIFF VIOLATIONS AUTOMATICALLY
----------------------------------------------------------------------
EOD;
    }
}
