<?php

namespace GrumPHP\Parser\Php\Visitor;

use PhpParser\Node;

/**
 * Class DeclareStrictTypesVisitor
 *
 * @package GrumPHP\Parser\Php\Visitor
 */
class DeclareStrictTypesVisitor extends AbstractVisitor
{
    /**
     * @var bool
     */
    private $hasStrictType = false;

    /**
     * @param Node $node
     *
     * @return void
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Declare_) {
            return;
        }

        foreach ($node->declares as $id => $declare) {
            if ($declare->key !== 'strict_types') {
                continue;
            }

            $this->hasStrictType = $declare->value->value === 1;
        }
    }

    /**
     * @param array $nodes
     *
     * @return void
     */
    public function afterTraverse(array $nodes)
    {
        if (!$this->hasStrictType) {
            $this->addError('No "declare(strict_types = 1)" found in file!');
        }
    }
}
