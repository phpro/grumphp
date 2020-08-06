<?php

declare(strict_types=1);

namespace GrumPHP\Exception;

class ExecutableNotFoundException extends RuntimeException
{
    public static function forCommand(string $command): self
    {
        return new self(
            sprintf('The executable for "%s" could not be found.', $command)
        );
    }
}
