<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\Php\PhpParserError;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

class ConcreteMethodCallVisitor extends NodeVisitorAbstract
{
    protected $concreteMethods;
    protected $filename;

    public function init($filename, array $keywords, ParseErrorsCollection $errors)
    {
        $this->concreteMethods = array();

        foreach ($keywords as $ident) {
            $letters = str_split($ident);

            if ($letters[0] == '-' && $letters[1] == '>' && end($letters) == '(') {
                // concrete method call
                $this->concreteMethods[] = substr($ident, 2, -1);
            }
        }

        $this->filename = $filename;
        $this->errors   = $errors;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall
            && is_string($node->name)
        ) {
            $method = $node->name;
            if (in_array($method, $this->concreteMethods)) {
                $this->errors->add(
                    new PhpParserError(
                        PhpParserError::TYPE_WARNING,
                        sprintf(
                            'Found "%s" method call',
                            $method
                        ),
                        $this->filename,
                        $node->getLine()
                    )
                );
            }
        }
    }
}
