<?php

declare(strict_types=1);

namespace GrumPHP\Parser\Php\Visitor;

use GrumPHP\Parser\Php\Context\ParserContext;
use PhpParser\NodeVisitor;

interface ContextAwareVisitorInterface extends NodeVisitor
{
    public function setContext(ParserContext $context): void;
}
