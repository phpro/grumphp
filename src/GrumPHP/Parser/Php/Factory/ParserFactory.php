<?php

namespace GrumPHP\Parser\Php\Factory;

use GrumPHP\Task\PhpParser;
use PhpParser\ParserFactory as PhpParserFactory;

/**
 * Class ParserFactory
 *
 * @package GrumPHP\Parser\Php\Factory
 */
class ParserFactory
{
    /**
     * @param array $options
     *
     * @return \PhpParser\Parser
     */
    public function createFromOptions(array $options)
    {
        $kind = ($options['kind'] === PhpParser::KIND_PHP5)
            ? PhpParserFactory::PREFER_PHP5 : PhpParserFactory::PREFER_PHP7;

        return (new PhpParserFactory())->create($kind);
    }
}
