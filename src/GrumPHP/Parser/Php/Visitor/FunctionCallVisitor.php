<?php

namespace GrumPHP\Parser\Php\Visitor;

use PhpParser\Node;

class FunctionCallVisitor extends NodeVisitorAbstract
{
    protected $functions = [];

    public function __construct(\GrumPHP\Configuration\GrumPHP $grumPHP)
    {
        parent::__construct($grumPHP);

        if (!empty($this->blacklist)) {
            $this->functions = array_merge($this->functions, $this->blacklist);
        }
        if (!empty($this->whitelist)) {
            $this->functions = array_merge($this->functions, $this->whitelist);
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\FuncCall) {
            $function = $node->name;
            if (in_array($function, $this->functions)) {
                $this->addError(
                    sprintf('Found "%s" function call', $function),
                    $function,
                    $node
                );
            }
        }
    }
}
