<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use PhpParser\Node;

class NoExitStatementsVisitor extends AbstractVisitor
{
    /**
     * @param Node $node
     *
     * @return void
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Expr\Exit_) {
            return;
        }

        $this->addError(
            sprintf('Found a forbidden exit statement.'),
            $node->getLine(),
            ParseError::TYPE_ERROR
        );
    }
}
