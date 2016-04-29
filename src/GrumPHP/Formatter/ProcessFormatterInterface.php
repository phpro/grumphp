<?php
namespace GrumPHP\Formatter;

use Symfony\Component\Process\Process;

/**
 * Class RawProcessFormatter
 *
 * @package GrumPHP\Formatter
 */
interface ProcessFormatterInterface
{
    /**
     * @param Process $process
     *
     * @return string
     */
    public function format(Process $process);
}
