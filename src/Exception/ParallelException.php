<?php

declare(strict_types=1);

namespace GrumPHP\Exception;

class ParallelException extends RuntimeException
{
    public static function fromThrowable(\Throwable $error)
    {
        return new self($error->getMessage(), $error->getCode(), $error);
    }

    public static function fromVerboseThrowable(\Throwable $error)
    {
        return new self(
            $error->getMessage() . PHP_EOL . $error->getTraceAsString() . PHP_EOL . $error->getPrevious(),
            $error->getCode(),
            $error
        );
    }
}
