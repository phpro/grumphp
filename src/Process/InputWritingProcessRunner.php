<?php

declare(strict_types=1);

namespace GrumPHP\Process;

use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class InputWritingProcessRunner
{
    /**
     * @param callable(): \Generator<array-key, string, mixed, void> $writer
     */
    public static function run(Process $process, callable $writer): int
    {
        $inputStream = new InputStream();
        $process->setInput($inputStream);
        $process->start();
        foreach ($writer() as $input) {
            $inputStream->write($input);
        }

        $inputStream->close();

        return $process->wait();
    }
}
