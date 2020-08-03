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
     * @param int[] $successExitCodes
     *
     * @return callable(): FixResult
     */
    public static function provide(string $command, array $successExitCodes = [0]): callable
    {
        return new SerializableClosure(
            static function () use ($command, $successExitCodes): FixResult {
                $process = Process::fromShellCommandline($command);
                $process->run();

                if (!in_array($process->getExitCode(), $successExitCodes, true)) {
                    return FixResult::failed(FixerException::fromProcess($process));
                }

                return FixResult::success($process->getOutput());
            }
        );
    }
}
