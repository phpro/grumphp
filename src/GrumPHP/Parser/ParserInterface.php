<?php

namespace GrumPHP\Parser;

use GrumPHP\Collection\NodeVisitorsCollection;
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
     * @param NodeVisitorsCollection $visitors
     *
     * @return ParseErrorsCollection
     */
    public function parse(SplFileInfo $file, array $keywords, NodeVisitorsCollection $visitors);

    /**
     * @return bool
     */
    public function isInstalled();
}
