<?php

namespace GrumPHP\Util;

class Str
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
}
