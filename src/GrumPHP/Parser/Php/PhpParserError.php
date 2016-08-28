<?php

namespace GrumPHP\Parser\Php;

use GrumPHP\Parser\ParseError;
use PhpParser\Error;

/**
 * Class PhpParserError
 *
 * @package GrumPHP\Parser\Php
 */
class PhpParserError extends ParseError
{
    /**
     * @param Error  $exception
     * @param string $filename
     *
     * @return PhpParserError
     */
    public static function fromParseException(Error $exception, $filename)
    {
        return new self(
            ParseError::TYPE_FATAL,
            $exception->getRawMessage(),
            $filename,
            $exception->getStartLine()
        );
    }
}
