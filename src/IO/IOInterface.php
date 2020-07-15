<?php

declare(strict_types=1);

namespace GrumPHP\IO;

use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Style\StyleInterface;

interface IOInterface
{
    public function isInteractive(): bool;

    public function isVerbose(): bool;

    public function isVeryVerbose(): bool;

    public function isDebug(): bool;

    public function isDecorated(): bool;

    /**
     * @return void
     */
    public function write(array $messages, bool $newline = true);

    /**
     * @return void
     */
    public function writeError(array $messages, bool $newline = true);

    public function style(): StyleInterface;

    public function section(): ConsoleSectionOutput;

    public function colorize(array $messages, string $color): array;
}
