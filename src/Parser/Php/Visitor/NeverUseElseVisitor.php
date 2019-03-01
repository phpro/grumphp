<?php

declare(strict_types=1);

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use PhpParser\Node;

class NeverUseElseVisitor extends AbstractVisitor
{
    /**
     * @see http://www.slideshare.net/rdohms/your-code-sucks-lets-fix-it-15471808
     * @see http://www.slideshare.net/guilhermeblanco/object-calisthenics-applied-to-php
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Else_ && !$node instanceof Node\Stmt\ElseIf_) {
            return;
        }

        $this->addError(
            sprintf(
                'Object Calisthenics error: Do not use the "%s" keyword!',
                $node instanceof  Node\Stmt\ElseIf_ ? 'elseif' : 'else'
            ),
            $node->getLine(),
            ParseError::TYPE_ERROR
        );
    }
}
