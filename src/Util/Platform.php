<?php

declare(strict_types=1);

namespace GrumPHP\Util;

class Platform
{
    /**
     * Windows has a limit on command line input strings.
     * This one is causing external commands to fail with exit code 1 without any error.
     * More information:.
     *
     * @see https://support.microsoft.com/en-us/kb/830473
     */
    const WINDOWS_COMMANDLINE_STRING_LIMITATION = 8191;

    public static function isWindows(): bool
    {
        return \defined('PHP_WINDOWS_VERSION_BUILD');
    }
}
