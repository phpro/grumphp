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
     * @var non-empty-string
     */
    protected $regex;

    /**
     * Regex constructor.
     * @param non-empty-string $string
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

    /**
     * @param non-empty-string $string
     * @return non-empty-string
     */
    private function toRegex(string $string): string
    {
        /** @var non-empty-string */
        return $this->isRegex($string) ? $string : Glob::toRegex($string);
    }

    public function addPatternModifier(string $modifier): void
    {
        /** @psalm-suppress InvalidLiteralArgument */
        if ('' === $modifier || !str_contains(self::ALLOWED_MODIFIERS, $modifier)) {
            throw new RuntimeException('Invalid regex modifier: '.$modifier);
        }

        // Find all modifiers of current regex:
        $modifiersPattern = '/(['.self::ALLOWED_MODIFIERS.']*$)/';
        preg_match($modifiersPattern, $this->regex, $matches);
        $modifiers = $matches[0];

        // Skip if the modifier is already available
        if (str_contains($modifiers, $modifier)) {
            return;
        }

        $this->regex .= $modifier;
    }

    /**
     * Returns the new regex.
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return $this->regex;
    }
}
