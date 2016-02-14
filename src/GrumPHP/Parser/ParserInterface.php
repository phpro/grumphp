<?php

namespace GrumPHP\Parser;

use GrumPHP\Collection\ParseErrorsCollection;
use SplFileInfo;

/**
 * Interface ParserInterface
 *
 * @package GrumPHP\Parser
 */
interface ParserInterface
{
    /**
     * @param SplFileInfo $file
     * @param array       $keywords
     *
     * @return ParseErrorsCollection
     */
    public function parse(SplFileInfo $file, array $keywords);

    /**
     * @return bool
     */
    public function isInstalled();
}
