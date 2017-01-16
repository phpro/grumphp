<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\Php\Context\ParserContext;
use PhpParser\NodeVisitor;

interface ContextAwareVisitorInterface extends NodeVisitor
{
    /**
     * @param ParserContext $context
     */
    public function setContext(ParserContext $context);
}
