<?php

declare(strict_types=1);

namespace GrumPHP\Process;

/**
 *
 * @internal
 *
 */
final class ProcessUtils
{
    /**
     * Escapes a string to be used as a shell argument.
     * Taken from the Symfony 4.0 package.
     * @todo Remove when Symfony's Process can accept an array of parameters (3.3+)
     *       From that moment on, we can remove this class, and directly pass
     *       an array of unescaped cli options as the `$commandline` argument
     *       in its constructor.
     * @author Martin Hasoň <martin.hason@gmail.com>
     *
     * @see \Symfony\Component\Process\Process::escapeArgument()
     */
    public static function escapeArgument(string $argument): string
    {
        if ('\\' !== DIRECTORY_SEPARATOR) {
            return "'".str_replace("'", "'\\''", $argument)."'";
        }
        if ('' === $argument = (string) $argument) {
            return '""';
        }
        if (false !== strpos($argument, "\0")) {
            $argument = str_replace("\0", '?', $argument);
        }
        if (!preg_match('/[\/()%!^"<>&|\s]/', $argument)) {
            return $argument;
        }
        $argument = preg_replace('/(\\\\+)$/', '$1$1', $argument);

        return '"'.str_replace(['"', '^', '%', '!', "\n"], ['""', '"^^"', '"^%"', '"^!"', '!LF!'], $argument).'"';
    }

    public static function escapeArguments(array $arguments): string
    {
        return implode(' ', array_map(
            function (string $argument): string {
                return self::escapeArgument($argument);
            },
            array_filter($arguments)
        ));
    }

    public static function escapeArgumentsFromString(string $arguments): string
    {
        return self::escapeArguments(explode(' ', $arguments));
    }
}
