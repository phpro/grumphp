<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Environment;

/**
 * Make sure that a folder is prepended to your current PATH env var.
 * This way, it first tries to detect dependencies in the folder they will most likely be.
 *
 * Code copied and split from composer:
 * @see https://github.com/composer/composer/blob/1.1/src/Composer/EventDispatcher/EventDispatcher.php#L147-L160
 */
class PathsRegistrar
{
    public static function prepend(string ...$paths): void
    {
        if (!array_key_exists(self::pathVarName(), $_SERVER)) {
            return;
        }

        // Reverse the paths so that the one added first in also loaded as first.
        foreach (array_reverse($paths) as $path) {
            self::prependOne($path);
        }
    }

    private static function prependOne(string $path): void
    {
        if (!is_dir($path) || self::pathContainsDir($path)) {
            return;
        }

        $pathStr = self::pathVarName();
        $_SERVER[$pathStr] = realpath($path).PATH_SEPARATOR.(string)getenv($pathStr);
        putenv($pathStr.'='.$_SERVER[$pathStr]);
    }

    /**
     * Detect which path variable name is being used
     */
    private static function pathVarName(): string
    {
        static $pathStr;
        if ($pathStr) {
            return $pathStr;
        }

        $pathStr = 'PATH';
        if (!isset($_SERVER[$pathStr]) && isset($_SERVER['Path'])) {
            $pathStr = 'Path';
        }

        return $pathStr;
    }

    private static function pathContainsDir(string $dir): bool
    {
        return (bool) preg_match(
            '{(^|'.PATH_SEPARATOR.')'.preg_quote($dir).'($|'.PATH_SEPARATOR.')}',
            (string) ($_SERVER[self::pathVarName()] ?? '')
        );
    }
}
