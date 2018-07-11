<?php

namespace GrumPHP\Parser\Php\Visitor;

use PhpParser\Node;

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
            // In PhpParser 3 and lower the key used in a `declare()` statement
            // is represented as a string value. Starting with PhpParser 4 this
            // key is represented by a 'PhpParser\Node\Identifier' object. To
            // support backwards compatibility with older versions the object
            // can be cast to a string to get the original string value.
            if ((string) $declare->key !== 'strict_types') {
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
