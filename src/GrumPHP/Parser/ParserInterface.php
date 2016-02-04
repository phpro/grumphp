<?php

namespace GrumPHP\Parser;

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
     * @return GrumPHP\Collection\ParseErrorsCollection
     */
    public function parse($filename, array $keywords);

    /**
     * @return bool
     */
    public function isInstalled();
}
