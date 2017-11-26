<?php declare(strict_types=1);

namespace GrumPHP\Parser\Php\Factory;

use GrumPHP\Parser\Php\Configurator\TraverserConfigurator;
use GrumPHP\Parser\Php\Context\ParserContext;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;

class TraverserFactory
{
    /**
     * @var TraverserConfigurator
     */
    private $configurator;

    /**
     * TraverserFactory constructor.
     */
    public function __construct(TraverserConfigurator $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * @throws \GrumPHP\Exception\RuntimeException
     */
    public function createForTaskContext(array $parserOptions, ParserContext $context): NodeTraverserInterface
    {
        $this->configurator->registerOptions($parserOptions);
        $this->configurator->registerContext($context);

        $traverser = new NodeTraverser();
        $this->configurator->configure($traverser);

        return $traverser;
    }
}
