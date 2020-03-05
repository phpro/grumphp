<?php

declare(strict_types=1);

namespace GrumPHP\Fixer\Provider;

use GrumPHP\Exception\FixerException;
use GrumPHP\Fixer\FixResult;
use Opis\Closure\SerializableClosure;
use Symfony\Component\Process\Process;

class FixableProcessProvider
{
    /**
     * @param Process $process
     *
     * @return callable(): FixResult
     */
    public static function provide(string $command): callable
    {
        return new SerializableClosure(
            static function () use ($command): FixResult {
                $process = Process::fromShellCommandline($command);
                $process->run();

                if (!$process->isSuccessful()) {
                    return FixResult::failed(FixerException::fromProcess($process));
                }

                return FixResult::success($process->getOutput());
            }
        );
    }
}
