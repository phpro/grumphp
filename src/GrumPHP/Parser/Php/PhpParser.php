<?php

namespace GrumPHP\Parser\Php;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\ParserInterface;
use GrumPHP\Parser\Php\Context\ParserContext;
use GrumPHP\Parser\Php\Factory\ParserFactory;
use GrumPHP\Parser\Php\Factory\TraverserFactory;
use PhpParser\Error;
use PhpParser\Parser;
use SplFileInfo;

/**
 * Class PhpParser
 *
 * @package GrumPHP\Parser\Php
 */
class PhpParser implements ParserInterface
{
    /**
     * @var ParserFactory
     */
    private $parserFactory;

    /**
     * @var TraverserFactory
     */
    private $traverserFactory;

    /**
     * @var array
     */
    private $parserOptions = [];

    /**
     * PhpParser constructor.
     *
     * @param ParserFactory    $parserFactory
     * @param TraverserFactory $traverserFactory
     */
    public function __construct(ParserFactory $parserFactory, TraverserFactory $traverserFactory)
    {
        $this->parserFactory = $parserFactory;
        $this->traverserFactory = $traverserFactory;
    }

    /**
     * @param array $options
     */
    public function setParserOptions(array $options)
    {
        $this->parserOptions = $options;
    }

    /**
     * @param SplFileInfo $file
     *
     * @return ParseErrorsCollection
     */
    public function parse(SplFileInfo $file)
    {
        $errors = new ParseErrorsCollection();
        $context = new ParserContext($file, $errors);
        $parser = $this->parserFactory->createFromOptions($this->parserOptions);
        $traverser = $this->traverserFactory->createForTaskContext($this->parserOptions, $context);

        try {
            $code = file_get_contents($file->getRealPath());
            $stmts = $parser->parse($code);
            $traverser->traverse($stmts);
        } catch (Error $e) {
            $errors->add(PhpParserError::fromParseException($e, $file->getRealPath()));
        }

        return $errors;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return interface_exists(Parser::class);
    }
}
