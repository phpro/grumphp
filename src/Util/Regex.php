<?php

namespace GrumPHP\Util;

use Symfony\Component\Finder\Glob;
use RuntimeException;

class Regex
{
    /**
     * A list of all allowed regex modifiers in PHP.
     */
    const ALLOWED_MODIFIERS = 'imsxuADU';

    /**
     * @var string
     */
    protected $regex;

    /**
     * Regex constructor.
     *
     * @param $string
     */
    public function __construct($string)
    {
        $this->regex = $this->toRegex($string);
    }

    /**
     * Checks whether the string is a regex.
     *
     * @param string $string
     *
     * @return bool Whether the given string is a regex
     */
    private function isRegex($string)
    {
        if (preg_match('/^(.{3,}?)[' . self::ALLOWED_MODIFIERS . ']*$/', $string, $m)) {
            $start = substr($m[1], 0, 1);
            $end = substr($m[1], -1);

            if ($start === $end) {
                return !preg_match('/[*?[:alnum:] \\\\]/', $start);
            }

            foreach ([['{', '}'], ['(', ')'], ['[', ']'], ['<', '>']] as $delimiters) {
                if ($start === $delimiters[0] && $end === $delimiters[1]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function toRegex($string)
    {
        return $this->isRegex($string) ? $string : Glob::toRegex($string);
    }

    /**
     * @param string $modifier
     */
    public function addPatternModifier($modifier)
    {
        if (!strlen($modifier) == 1 || !strstr(self::ALLOWED_MODIFIERS, $modifier)) {
            throw new RuntimeException('Invalid regex modifier: ' . $modifier);
        }

        // Find all modifiers of current regex:
        $modifiersPattern = '/([' . self::ALLOWED_MODIFIERS . ']*$)/';
        preg_match($modifiersPattern, $this->regex, $matches);
        $modifiers = $matches[0];

        // Skip if the modifier is already available
        if (strstr($modifiers, $modifier) !== false) {
            return;
        }

        $this->regex .= $modifier;
    }

    /**
     * Returns the new regex.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->regex;
    }
}
