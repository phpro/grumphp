<?php declare(strict_types=1);

namespace GrumPHP\Linter;

use GrumPHP\Collection\LintErrorsCollection;
use SplFileInfo;

interface LinterInterface
{
    /**
     * @return LintErrorsCollection|mixed
     */
    public function lint(SplFileInfo $file);

    /**
     * @return bool
     */
    public function isInstalled(): bool;
}
