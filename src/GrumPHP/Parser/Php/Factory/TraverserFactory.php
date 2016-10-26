<?php

namespace GrumPHP\Parser\Php\Factory;

use GrumPHP\Parser\Php\Context\ParserContext;
use GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

/**
 * Class TraverserFactory
 *
 * @package GrumPHP\Parser\Php\Factory
 */
class TraverserFactory
{
    /**
     * @var NodeVisitor[]
     */
    private $visitors = [];

    /**
     * @param NodeVisitor $visitor
     */
    public function addNodeVisitor(NodeVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * @param ParserContext $context
     *
     * @return NodeTraverser
     */
    public function createForContext(ParserContext $context)
    {
        $traverser = new NodeTraverser();
        foreach ($this->visitors as $visitor) {
            $traverser->addVisitor($visitor);

            if ($visitor instanceof ContextAwareVisitorInterface) {
                $visitor->setContext($context);
            }
        }

        return $traverser;
    }
}
