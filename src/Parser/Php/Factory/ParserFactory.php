<?php

declare(strict_types=1);

namespace GrumPHP\Parser\Php\Factory;

use GrumPHP\Task\PhpParser;
use PhpParser\ParserFactory as PhpParserFactory;

class ParserFactory
{
    public function createFromOptions(array $options): \PhpParser\Parser
    {
        $kind = (PhpParser::KIND_PHP5 === $options['kind'])
            ? PhpParserFactory::PREFER_PHP5 : PhpParserFactory::PREFER_PHP7;

        return (new PhpParserFactory())->create($kind);
    }
}
