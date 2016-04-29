<?php

namespace GrumPHP\Formatter;

use Symfony\Component\Process\Process;

/**
 * Class RawProcessFormatter
 *
 * @package GrumPHP\Formatter
 */
class RawProcessFormatter implements ProcessFormatterInterface
{
    /**
     * This method will format the output of a Process object to a string.
     *
     * @param Process $process
     *
     * @return string
     */
    public function format(Process $process)
    {
        $stdout = $process->getOutput();
        $stderr = $process->getErrorOutput();

        return trim($stdout . PHP_EOL . $stderr);
    }
}
