<?php

declare(strict_types=1);

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use PhpParser\Node;

class NoExitStatementsVisitor extends AbstractVisitor
{
    public function leaveNode(Node $node): void
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
