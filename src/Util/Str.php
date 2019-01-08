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
    public static function containsOneOf($haystack, array $needles)
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Split $string on $delimiter and trim the individual parts
     *
     * @param string $delimiter
     * @param string $string
     * @return string[]
     */
    public static function explodeWithCleanup(string $delimiter, string $string): array
    {
        $stringValues = explode($delimiter, $string);
        $parsedValues = [];
        foreach ($stringValues as $k => $v) {
            $v = trim($v);
            if (empty($v)) {
                continue;
            }
            $parsedValues[] = $v;
        }
        return $parsedValues;
    }
}
