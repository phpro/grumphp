<?php declare(strict_types=1);

namespace GrumPHP\Formatter;

use Symfony\Component\Process\Process;

class RawProcessFormatter implements ProcessFormatterInterface
{
    /**
     * This method will format the output of a Process object to a string.
     */
    public function format(Process $process): string
    {
        $stdout = $process->getOutput();
        $stderr = $process->getErrorOutput();

        return trim($stdout . PHP_EOL . $stderr);
    }
}
