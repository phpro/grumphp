<?php declare(strict_types=1);

namespace GrumPHP\Formatter;

use Symfony\Component\Process\Process;

/**
 * Class RawProcessFormatter
 */
interface ProcessFormatterInterface
{
    /**
     * @return string|null
     */
    public function format(Process $process);
}
