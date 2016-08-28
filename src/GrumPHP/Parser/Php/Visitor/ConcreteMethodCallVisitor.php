<?php

namespace GrumPHP\Parser\Php\Visitor;

use PhpParser\Node;

class ConcreteMethodCallVisitor extends NodeVisitorAbstract
{
    protected $concreteMethods = [];

    public function __construct(\GrumPHP\Configuration\GrumPHP $grumPHP)
    {
        parent::__construct($grumPHP);

        if (!empty($this->blacklist)) {
            $this->concreteMethods = array_merge($this->concreteMethods, $this->blacklist);
        }
        if (!empty($this->whitelist)) {
            $this->concreteMethods = array_merge($this->concreteMethods, $this->whitelist);
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall
            && is_string($node->name)
        ) {
            $method = $node->name;
            if (in_array($method, $this->concreteMethods)) {
                $this->addError(
                    sprintf('Found "%s" method call', $method),
                    $method,
                    $node
                );
            }
        }
    }
}
