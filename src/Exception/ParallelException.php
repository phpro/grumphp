<?php

declare(strict_types=1);

namespace GrumPHP\Exception;

class ParallelException extends RuntimeException
{
    public static function fromThrowable(\Throwable $error): self
    {
        return new self($error->getMessage(), (int)$error->getCode(), $error);
    }

    public static function fromVerboseThrowable(\Throwable $error): self
    {
        return new self(
            $error->getMessage() . PHP_EOL . $error->getTraceAsString() . PHP_EOL . (string) $error->getPrevious(),
            (int)$error->getCode(),
            $error
        );
    }
}
