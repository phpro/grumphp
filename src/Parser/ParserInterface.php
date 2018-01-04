<?php declare(strict_types=1);

namespace GrumPHP\Parser;

use GrumPHP\Collection\ParseErrorsCollection;
use SplFileInfo;

interface ParserInterface
{
    /**
     * @return ParseErrorsCollection
     */
    public function parse(SplFileInfo $file): ParseErrorsCollection;

    /**
     * @return bool
     */
    public function isInstalled(): bool;
}
