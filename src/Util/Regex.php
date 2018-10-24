<?php

declare(strict_types=1);

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
     */
    public function __construct(string $string)
    {
        $this->regex = $this->toRegex($string);
    }

    /**
     * Checks whether the string is a regex.
     */
    private function isRegex(string $string): bool
    {
        if (preg_match('/^(.{3,}?)['.self::ALLOWED_MODIFIERS.']*$/', $string, $m)) {
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

    private function toRegex(string $string): string
    {
        return $this->isRegex($string) ? $string : Glob::toRegex($string);
    }

    public function addPatternModifier(string $modifier)
    {
        if ('' === $modifier || false === strpos(self::ALLOWED_MODIFIERS, $modifier)) {
            throw new RuntimeException('Invalid regex modifier: '.$modifier);
        }

        // Find all modifiers of current regex:
        $modifiersPattern = '/(['.self::ALLOWED_MODIFIERS.']*$)/';
        preg_match($modifiersPattern, $this->regex, $matches);
        $modifiers = $matches[0];

        // Skip if the modifier is already available
        if (false !== strpos($modifiers, $modifier)) {
            return;
        }

        $this->regex .= $modifier;
    }

    /**
     * Returns the new regex.
     */
    public function __toString(): string
    {
        return $this->regex;
    }
}
