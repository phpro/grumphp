<?php

namespace GrumPHP\Parser\Php;

use GrumPHP\Collection\ParseErrorsCollection;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use SplFileInfo;

class NodeVisitor extends NodeVisitorAbstract
{
    protected $staticMethods;
    protected $concreteMethods;
    protected $functions;
    protected $filename;

    public function __construct($filename, array $keywords, ParseErrorsCollection $errors)
    {
        $this->staticMethods   = array();
        $this->concreteMethods = array();
        $this->functions       = array();

        foreach ($keywords as $ident) {

            $letters = str_split($ident);

            if ($letters[0] == ':' && $letters[1] == ':' && end($letters) == '(') {
                $this->staticMethods[] = substr($ident, 2, -1);

            } elseif ($letters[0] == '-' && $letters[1] == '>' && end($letters) == '(') {
                $this->concreteMethods[] = substr($ident, 2, -1);

            } elseif (end($letters) == '(') {
                $this->functions[] = substr($ident, 0, -1);
            }
        }

        $this->filename = $filename;
        $this->errors   = $errors;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\FuncCall) {
            $function = $node->name;
            if (in_array($function, $this->functions)) {
                $this->errors->add(
                    sprintf(
                        'Function %s found in %s on line %d',
                        $function,
                        $this->filename,
                        $node->getLine()
                    )
                );
            }

        } elseif ($node instanceof Node\Expr\MethodCall
            && is_string($node->name)
        ) {
            $method = $node->name;
            if (in_array($method, $this->concreteMethods)) {
                $this->errors->add(
                    sprintf(
                        'Concrete method %s found in %s on line %d',
                        $method,
                        $this->filename,
                        $node->getLine()
                    )
                );
            }

        } elseif ($node instanceof Node\Expr\StaticCall
            && is_string($node->name)
        ) {
            $method = $node->name;
            if (in_array($method, $this->staticMethods)) {
                $this->errors->add(
                    sprintf(
                        'Static method %s found in %s on line %d',
                        $method,
                        $this->filename,
                        $node->getLine()
                    )
                );
            }
        }
    }
}
