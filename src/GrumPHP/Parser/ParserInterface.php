<?php

namespace GrumPHP\Parser;

use GrumPHP\Collection\ParseErrorsCollection;

/**
 * Interface ParserInterface
 *
 * @package GrumPHP\Parser
 */
interface ParserInterface
{
    /**
     * @param string $filename
     *
     * @return ParseErrorsCollection
     */
    public function parse($filename, array $keywords);

    /**
     * @return bool
     */
    public function isInstalled();
}
