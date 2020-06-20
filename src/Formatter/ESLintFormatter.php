<?php

declare(strict_types=1);

namespace GrumPHP\Formatter;

use Symfony\Component\Process\Process;

class ESLintFormatter implements ProcessFormatterInterface
{
    public function format(Process $process): string
    {
        $stdout = $process->getOutput();
        $stderr = $process->getErrorOutput();

        return trim($stdout . PHP_EOL . $stderr);
    }

    public function formatErrorMessage(array $messages, array $suggestions): string
    {
        return sprintf(
            '%sYou can fix all errors by running following commands:%s',
            implode(PHP_EOL, $messages) . PHP_EOL . PHP_EOL,
            PHP_EOL . implode(PHP_EOL, $suggestions)
        );
    }
}
