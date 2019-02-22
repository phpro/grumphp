<?php

declare(strict_types=1);

namespace GrumPHP\IO;

interface IOInterface
{
    public function isInteractive(): bool;

    public function isVerbose(): bool;

    public function isVeryVerbose(): bool;

    public function isDebug(): bool;

    public function isDecorated(): bool;

    public function write(array $messages, bool $newline = true);

    public function writeError(array $messages, bool $newline = true);
}
