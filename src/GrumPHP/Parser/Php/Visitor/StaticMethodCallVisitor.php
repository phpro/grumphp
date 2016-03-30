<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\Php\PhpParserError;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

class StaticMethodCallVisitor extends NodeVisitorAbstract
{
    protected $staticMethods;
    protected $filename;

    public function init($filename, array $keywords, ParseErrorsCollection $errors)
    {
        $this->staticMethods = array();

        foreach ($keywords as $ident) {
            $letters = str_split($ident);

            if ($letters[0] == ':' && $letters[1] == ':' && end($letters) == '(') {
                // static method call
                $this->staticMethods[] = substr($ident, 2, -1);
            }
        }

        $this->filename = $filename;
        $this->errors   = $errors;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\StaticCall
            && is_string($node->name)
        ) {
            $method = $node->name;
            if (in_array($method, $this->staticMethods)) {
                $this->errors->add(
                    new PhpParserError(
                        PhpParserError::TYPE_WARNING,
                        sprintf(
                            'Found "%s" static method call',
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
