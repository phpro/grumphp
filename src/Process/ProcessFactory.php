<?php

declare(strict_types=1);

namespace GrumPHP\Process;

use GrumPHP\Collection\ProcessArgumentsCollection;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class ProcessFactory
{
    public static function fromArguments(ProcessArgumentsCollection $arguments): Process
    {
        return new Process($arguments->getValues());
    }

    /**
     * @param array|string $arguments
     */
    public static function fromScalar($arguments): Process
    {
        return is_array($arguments) ? new Process($arguments) : Process::fromShellCommandline((string) $arguments);
    }
}
