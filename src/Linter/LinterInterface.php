<?php declare(strict_types=1);

namespace GrumPHP\Linter;

use GrumPHP\Collection\LintErrorsCollection;
use SplFileInfo;

interface LinterInterface
{
    /**
     * @param SplFileInfo $file
     *
     * @return LintErrorsCollection
     */
    public function lint(SplFileInfo $file);

    /**
     * @return bool
     */
    public function isInstalled();
}
