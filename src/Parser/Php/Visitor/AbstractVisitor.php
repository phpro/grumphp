<?php

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\ParseError;
use GrumPHP\Parser\Php\Context\ParserContext;
use GrumPHP\Parser\Php\PhpParserError;
use PhpParser\NodeVisitorAbstract;

class AbstractVisitor extends NodeVisitorAbstract implements ContextAwareVisitorInterface
{
    /**
     * @var ParserContext
     */
    protected $context;

    /**
     * @param ParserContext $context
     */
    public function setContext(ParserContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param string $message
     * @param int    $line
     * @param string $type
     */
    protected function addError($message, $line = -1, $type = ParseError::TYPE_ERROR)
    {
        $errors = $this->context->getErrors();
        $fileName = $this->context->getFile()->getRealPath();
        $errors->add(new PhpParserError($type, $message, $fileName, $line));
    }
}
