<?php declare(strict_types=1);

namespace GrumPHP\Parser\Php;

use GrumPHP\Parser\ParseError;
use PhpParser\Error;

class PhpParserError extends ParseError
{
    /**
     * @return PhpParserError
     */
    public static function fromParseException(Error $exception, string $filename): self
    {
        return new self(
            ParseError::TYPE_FATAL,
            $exception->getRawMessage(),
            $filename,
            $exception->getStartLine()
        );
    }
}
