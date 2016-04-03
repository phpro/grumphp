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
     * @var NodeVisitorsCollection|NodeVisitor[]
     */
    protected $nodeVisitors;

    public function __construct()
    {
        $this->nodeVisitors = new NodeVisitorsCollection();
    }

    /**
     * @param NodeVisitor $visitor
     * @param array       $options
     */
    public function addNodeVisitor(NodeVisitor $visitor)
    {
        if ($this->nodeVisitors->contains($visitor)) {
            return;
        }

        $this->nodeVisitors->add($visitor);
    }

    /**
     * @return NodeVisitorsCollection|NodeVisitor[]
     */
    public function getNodeVisitors()
    {
        return $this->nodeVisitors;
    }

    /**
     * @param SplFileInfo $file
     *
     * @return GrumPHP\Collection\ParseErrorsCollection
     */
    public function parse(SplFileInfo $file)
    {
        $filename  = $file->getRealPath();

        $errors    = new ParseErrorsCollection();

        $parser    = new Parser(new Emulative);
        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NameResolver);

        foreach ($this->nodeVisitors as $visitor) {
            $visitor->init($filename, $errors);
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
