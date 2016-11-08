<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\Php\Context\ParserContext;
use PhpParser\NodeVisitor;

/**
 * Interface ContextAwareVisitorInterface
 *
 * @package GrumPHP\Parser\Php\Visitor
 */
interface ContextAwareVisitorInterface extends NodeVisitor
{
    /**
     * @param ParserContext $context
     */
    public function setContext(ParserContext $context);
}
