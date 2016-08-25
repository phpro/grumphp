<?php

namespace spec\GrumPHP\Formatter;

use GrumPHP\Formatter\PhpcsFormatter;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;

/**
 * @mixin PhpcsFormatter
 */
class PhpcsFormatterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Formatter\PhpcsFormatter');
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

    function it_formats_phpcs_json_output_for_single_file(Process $process)
    {
        $json = $this->parseJson(array(
            '/filePath' => array('messages' => array('fixable' => true), ),
        ));
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('');
    }

    function it_formats_phpcs_json_output_for_multiple_files(Process $process)
    {
        $json = $this->parseJson(array(
            '/filePath' => array('messages' => array('fixable' => true), ),
            '/filePath2' => array('messages' => array('fixable' => false), ),
        ));
        $process->getOutput()->willReturn($json);
        $this->format($process)->shouldBe('');
    }

    /**
     * @param $files
     *
     * @return string
     */
    private function parseJson(array $files)
    {
        return PHP_EOL . json_encode(array(
            'totals' => array(
                'fixable' => 1
            ),
            'files' => $files
        ));
    }
}
