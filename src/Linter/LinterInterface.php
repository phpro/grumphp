<?php

declare(strict_types=1);

namespace GrumPHP\Linter;

use GrumPHP\Collection\LintErrorsCollection;
use SplFileInfo;

interface LinterInterface
{
    public function lint(string $file): LintErrorsCollection;

    public function isInstalled(): bool;
}
