<?php

namespace GrumPHP\Parser\Php\Visitor;

use PhpParser\Node;

class StaticMethodCallVisitor extends NodeVisitorAbstract
{
    protected $staticMethods = [];

    public function __construct(\GrumPHP\Configuration\GrumPHP $grumPHP)
    {
        parent::__construct($grumPHP);

        if (!empty($this->blacklist)) {
            $this->staticMethods = array_merge($this->staticMethods, $this->blacklist);
        }
        if (!empty($this->whitelist)) {
            $this->staticMethods = array_merge($this->staticMethods, $this->whitelist);
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\StaticCall
            && is_string($node->name)
        ) {
            $method = $node->name;
            if (in_array($method, $this->staticMethods)) {
                $this->addError(
                    sprintf('Found "%s" static method call', $method),
                    $method,
                    $node
                );
            }
        }
    }
}
