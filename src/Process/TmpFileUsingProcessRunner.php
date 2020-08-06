<?php

declare(strict_types=1);

namespace GrumPHP\Process;

use GrumPHP\Exception\ProcessException;
use Symfony\Component\Process\Process;

/**
 * This runner can be used to run a process whilst creating a file with temporary data.
 * Once the command has finished, the temporary file is removed.
 */
class TmpFileUsingProcessRunner
{
    /**
     * @param callable(string): Process $processBuilder
     * @param callable(): \Generator<array-key, string, mixed, void> $writer
     */
    public static function run(callable $processBuilder, callable $writer): Process
    {
        if (!$tmp = tmpfile()) {
            throw ProcessException::tmpFileCouldNotBeCreated();
        }

        $path = stream_get_meta_data($tmp)['uri'] ?? null;
        if (!$path) {
            throw ProcessException::tmpFileCouldNotBeCreated();
        }

        foreach ($writer() as $entry) {
            fwrite($tmp, (string) $entry);
        }
        fseek($tmp, 0);

        try {
            $process = $processBuilder($path);
            $process->run();
        } finally {
            fclose($tmp);
        }

        return $process;
    }
}
