<?php

declare(strict_types=1);

namespace GrumPHP\IO;

class NullIO implements IOInterface
{
    public function isInteractive(): bool
    {
        return false;
    }

    public function isVerbose(): bool
    {
        return false;
    }

    public function isVeryVerbose(): bool
    {
        return false;
    }

    public function isDebug(): bool
    {
        return false;
    }

    public function isDecorated(): bool
    {
        return false;
    }

    public function write(array $messages, bool $newline = true)
    {
    }

    public function writeError(array $messages, bool $newline = true)
    {
    }
}
