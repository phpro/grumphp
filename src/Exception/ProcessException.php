<?php

declare(strict_types=1);

namespace GrumPHP\Exception;

class ProcessException extends RuntimeException
{
    public static function tmpFileCouldNotBeCreated(): self
    {
        return new self(
            'The process requires a temporary file in order to run. We could not create one.'
            . 'Please check your ini setting!'
        );
    }
}
