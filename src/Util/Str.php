<?php

namespace GrumPHP\Util;

class Str
{
    /**
     * String contains one of the provided needles
     *
     * @param string $haystack
     * @param array $needles
     * @return bool
     */
    public static function containsOneOf(string $haystack, array $needles)
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
