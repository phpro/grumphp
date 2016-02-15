<?php

namespace GrumPHP\Parser\Php;

use GrumPHP\Collection\NodeVisitorsCollection;
use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\ParserInterface;
use PhpParser\Parser;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor;
use PhpParser\Error;
use SplFileInfo;

/**
 * Class PhpParser
 *
 * @package GrumPHP\Parser\Php
 */
class PhpParser implements ParserInterface
{
    /**
     * @param SplFileInfo $filename
     * @param array       $keywords
     * @param NodeVisitorsCollection $visitors
     *
     * @return GrumPHP\Collection\ParseErrorsCollection
     */
    public function parse(SplFileInfo $file, array $keywords, NodeVisitorsCollection $visitors)
    {
        $filename  = $file->getRealPath();

        $errors    = new ParseErrorsCollection();

        $parser    = new Parser(new Emulative);
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NameResolver);

        foreach ($visitors as $visitor) {
            $visitor->init($filename, $keywords, $errors);
            $traverser->addVisitor($visitor);
        }

        try {
            $code = $file->getContents();

            // parse
            $stmts = $parser->parse($code);

            // traverse
            $traverser->traverse($stmts);

        } catch (Error $e) {
            $errors[] = PhpParserError::fromParseException($e, $filename);
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
