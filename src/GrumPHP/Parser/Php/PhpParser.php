<?php

namespace GrumPHP\Parser\Php;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\ParserInterface;
use PhpParser\Parser;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Error;

/**
 * Class PhpParser
 *
 * @package GrumPHP\Parser\Php
 */
class PhpParser implements ParserInterface
{
    /**
     * @param string $filename
     *
     * @return GrumPHP\Collection\ParseErrorsCollection
     */
    public function parse($filename, array $keywords)
    {
        $errors    = new ParseErrorsCollection();

        $parser    = new Parser(new Emulative);
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NameResolver);
        $traverser->addVisitor(new NodeVisitor($filename, $keywords, $errors));

        try {
            $code = file_get_contents($filename);

            // parse
            $stmts = $parser->parse($code);

            // traverse
            $traverser->traverse($stmts);

        } catch (Error $e) {
            $errors->add($e->getMessage());
        }

        return $errors;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return class_exists('PhpParser\Parser');
    }
}
