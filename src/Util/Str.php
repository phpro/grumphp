<?php

declare(strict_types=1);

namespace GrumPHP\Util;

final class Str
{
    /**
     * String contains one of the provided needles
     */
    public static function containsOneOf(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Split $value on ",", trim the individual parts and
     * de-deduplicate the remaining values
     */
    public static function explodeWithCleanup(string $delimiter, string $value): array
    {
        return array_unique(array_map(function (string $value) {
            return trim($value);
        }, array_filter(explode($delimiter, $value))));
    }
}
