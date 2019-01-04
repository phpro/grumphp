<?php

declare(strict_types=1);

namespace GrumPHP\Formatter;

use Symfony\Component\Process\Process;

interface ProcessFormatterInterface
{
    public function format(Process $process): string;
}
