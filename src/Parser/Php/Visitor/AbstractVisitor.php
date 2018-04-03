<?php declare(strict_types=1);

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

    
    public function setContext(ParserContext $context)
    {
        $this->context = $context;
    }

    
    protected function addError(string $messageint , $line = string -1, $type = ParseError::TYPE_ERROR)
    {
        $errors = $this->context->getErrors();
        $fileName = $this->context->getFile()->getRealPath();
        $errors->add(new PhpParserError($type, $message, $fileName, $line));
    }
}
