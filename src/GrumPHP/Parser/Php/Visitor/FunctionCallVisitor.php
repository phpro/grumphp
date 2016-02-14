<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Parser\Php\PhpParserError;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

class FunctionCallVisitor extends NodeVisitorAbstract
{
    protected $functions;
    protected $filename;

    public function init($filename, array $keywords, ParseErrorsCollection $errors)
    {
        $this->functions = array();

        foreach ($keywords as $ident) {
            $letters = str_split($ident);

            if ($letters[0] !== ':' && $letters[0] !== '-' && end($letters) == '(') {
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
                    new PhpParserError(
                        PhpParserError::TYPE_WARNING,
                        sprintf(
                            'Found "%s" function call',
                            $function
                        ),
                        $this->filename,
                        $node->getLine()
                    )
                );
            }
        }
    }
}
