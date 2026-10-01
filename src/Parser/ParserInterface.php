<?php

declare(strict_types=1);

namespace GrumPHP\Parser;

use GrumPHP\Collection\ParseErrorsCollection;
use SplFileInfo;

interface ParserInterface
{
    public function parse(string $file): ParseErrorsCollection;

    public function isInstalled(): bool;
}
