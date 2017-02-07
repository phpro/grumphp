<?php

namespace GrumPHP\Parser\Php\Factory;

use GrumPHP\Parser\Php\Configurator\TraverserConfigurator;
use GrumPHP\Parser\Php\Context\ParserContext;
use PhpParser\NodeTraverser;

class TraverserFactory
{
    /**
     * @var TraverserConfigurator
     */
    private $configurator;

    /**
     * TraverserFactory constructor.
     *
     * @param TraverserConfigurator $configurator
     */
    public function __construct(TraverserConfigurator $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * @param array         $parserOptions
     * @param ParserContext $context
     *
     * @return NodeTraverser
     * @throws \GrumPHP\Exception\RuntimeException
     */
    public function createForTaskContext(array $parserOptions, ParserContext $context)
    {
        $this->configurator->registerOptions($parserOptions);
        $this->configurator->registerContext($context);

        $traverser = new NodeTraverser();
        $this->configurator->configure($traverser);

        return $traverser;
    }
}
