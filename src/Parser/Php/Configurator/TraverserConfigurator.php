<?php

declare(strict_types=1);

namespace GrumPHP\Parser\Php\Configurator;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Parser\Php\Context\ParserContext;
use GrumPHP\Parser\Php\Visitor\ConfigurableVisitorInterface;
use GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface;
use PhpParser\NodeTraverserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TraverserConfigurator
{
    /**
     * @var string[]
     */
    private $registeredVisitorIds = [];

    /**
     * @var array
     */
    private $standardEnabledVisitors = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ParserContext
     */
    private $context;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws \GrumPHP\Exception\RuntimeException
     */
    public function registerVisitorId(string $alias, string $visitorId)
    {
        if (array_key_exists($alias, $this->registeredVisitorIds)) {
            $registeredId = $this->registeredVisitorIds[$alias];
            throw new RuntimeException(
                sprintf('The visitor alias %s is already registered to visitor with id %s.', $alias, $registeredId)
            );
        }

        $this->registeredVisitorIds[$alias] = $visitorId;
    }

    /**
     * @throws \GrumPHP\Exception\RuntimeException
     */
    public function registerStandardEnabledVisitor(string $alias, array $visitorOptions = null)
    {
        if (array_key_exists($alias, $this->standardEnabledVisitors)) {
            throw new RuntimeException(
                sprintf('The visitor alias %s is already registered as a default enabled visitor.', $alias)
            );
        }

        $this->standardEnabledVisitors[$alias] = $visitorOptions;
    }

    public function registerOptions(array $options)
    {
        $this->options = $options;
    }

    public function registerContext(ParserContext $context)
    {
        $this->context = $context;
    }

    /**
     * @throws \GrumPHP\Exception\RuntimeException
     */
    public function configure(NodeTraverserInterface $traverser)
    {
        $this->guardTaskHasVisitors();
        $this->guardContextIsRegistered();

        $configuredVisitors = $this->loadEnabledVisitorsForCurrentOptions();
        $configuredVisitorIds = array_keys($configuredVisitors);
        $registeredVisitors = $this->registeredVisitorIds;
        $registeredVisitorsIds = array_keys($registeredVisitors);

        $visitorIds = array_values(array_intersect($registeredVisitorsIds, $configuredVisitorIds));
        $unknownConfiguredVisitorIds = array_diff($configuredVisitorIds, $registeredVisitorsIds);

        if (\count($unknownConfiguredVisitorIds)) {
            throw new RuntimeException(
                sprintf('Found unknown php_parser visitors: %s', implode(',', $unknownConfiguredVisitorIds))
            );
        }

        foreach ($visitorIds as $visitorAlias) {
            $visitorId = $registeredVisitors[$visitorAlias];
            $visitor = $this->container->get($visitorId);

            if ($visitor instanceof ContextAwareVisitorInterface) {
                $visitor->setContext($this->context);
            }

            $options = $configuredVisitors[$visitorAlias];
            if ($visitor instanceof ConfigurableVisitorInterface && \is_array($options)) {
                $visitor->configure($options);
            }

            $traverser->addVisitor($visitor);
        }

        // Reset context to make sure the next configure call will actually run in the correct context:
        $this->context = null;
    }

    private function loadEnabledVisitorsForCurrentOptions(): array
    {
        $visitors = $this->standardEnabledVisitors;
        foreach ($this->options['visitors'] as $alias => $visitorOptions) {
            $visitors[$alias] = $visitorOptions;
        }

        return $visitors;
    }

    /**
     * @throws \GrumPHP\Exception\RuntimeException
     */
    private function guardTaskHasVisitors()
    {
        if (!isset($this->options['visitors'])) {
            throw new RuntimeException('The parser context is not set. Please register it to the configurator!');
        }
    }

    /**
     * @throws \GrumPHP\Exception\RuntimeException
     */
    private function guardContextIsRegistered()
    {
        if (!$this->context instanceof ParserContext) {
            throw new RuntimeException('The parser context is not set. Please register it to the configurator!');
        }
    }
}
