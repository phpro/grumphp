<?php

declare(strict_types=1);

namespace GrumPHP\Process;

use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

/**
 * This runner can be used to run a process whilst writing data to STDIN
 */
class InputWritingProcessRunner
{
    /**
     * @param callable(): Process $processBuilder
     * @param callable(): \Generator<array-key, string, mixed, void> $writer
     */
    public static function run(callable $processBuilder, callable $writer): Process
    {
        $process = $processBuilder();
        $inputStream = new InputStream();
        $process->setInput($inputStream);
        $process->start();
        foreach ($writer() as $input) {
            $inputStream->write($input);
        }

        $inputStream->close();
        $process->wait();

        return $process;
    }
}
