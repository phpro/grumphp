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
        // @todo Remove backward compatibility layer as soon as Symfony Process accepts an array (3.3+).
        //       From then on, you can simply pass `$arguments->getValues()` directly as the first constructor argument.
        $commandlineArgs = array_map(function ($argument) {
            return ProcessUtils::escapeArgument($argument);
        }, $arguments->getValues());

        $commandline = implode(' ', $commandlineArgs);

        return new Process($commandline);
    }
}
