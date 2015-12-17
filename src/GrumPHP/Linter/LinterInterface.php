<?php

namespace GrumPHP\Linter;

use GrumPHP\Collection\LintErrorsCollection;
use SplFileInfo;

/**
 * Interface LinterInterface
 *
 * @package GrumPHP\Linter
 */
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
