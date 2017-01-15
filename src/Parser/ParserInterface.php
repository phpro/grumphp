<?php

namespace GrumPHP\Parser;

use GrumPHP\Collection\ParseErrorsCollection;
use SplFileInfo;

interface ParserInterface
{
    /**
     * @param SplFileInfo $file
     *
     * @return ParseErrorsCollection
     */
    public function parse(SplFileInfo $file);

    /**
     * @return bool
     */
    public function isInstalled();
}
