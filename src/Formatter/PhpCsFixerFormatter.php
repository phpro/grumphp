<?php

declare(strict_types=1);

namespace GrumPHP\Formatter;

use Symfony\Component\Process\Process;

class PhpCsFixerFormatter implements ProcessFormatterInterface
{
    /**
     * @var int
     */
    private $counter = 0;

    /**
     * Resets the internal counter.
     */
    public function resetCounter(): void
    {
        $this->counter = 0;
    }

    public function format(Process $process): string
    {
        $output = $process->getOutput();
        if (!$output) {
            return $process->getErrorOutput();
        }

        if (!$json = json_decode($output, true)) {
            return $output;
        }

        return $this->formatJsonResponse($json);
    }

    private function formatJsonResponse(array $json): string
    {
        $formatted = [];
        foreach ($json['files'] as $file) {
            if (!\is_array($file) || !isset($file['name'])) {
                $formatted[] = 'Invalid file: '.print_r($file, true);
                continue;
            }

            $formatted[] = $this->formatFile($file);
        }

        return implode(PHP_EOL, $formatted);
    }

    private function formatFile(array $file): string
    {
        if (!isset($file['name'])) {
            return 'Invalid file: '.print_r($file, true);
        }

        $hasFixers = isset($file['appliedFixers']);
        $hasDiff = isset($file['diff']);

        return sprintf(
            '%s) %s%s%s',
            ++$this->counter,
            $file['name'],
            $hasFixers ? ' ('.implode(', ', $file['appliedFixers']).')' : '',
            $hasDiff ? PHP_EOL.PHP_EOL.$file['diff'] : ''
        );
    }
}
