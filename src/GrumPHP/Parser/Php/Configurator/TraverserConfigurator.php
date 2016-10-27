<?php

namespace GrumPHP\Parser\Php\Configurator;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Parser\Php\Context\ParserContext;
use GrumPHP\Parser\Php\Visitor\ConfigurableVisitorInterface;
use GrumPHP\Parser\Php\Visitor\ContextAwareVisitorInterface;
use PhpParser\NodeTraverserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TraverserConfigurator
 *
 * @package GrumPHP\Parser\Php\Configurator
 */
class TraverserConfigurator
{

    const CONFIGURATION_PREFIX = 'grumphp.parser.php.visitor.';

    /**
     * @var string[]
     */
    private $registeredVisitorIds = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var ParserContext
     */
    private $context;

    /**
     * TraverserConfigurator constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $visitorId
     */
    public function registerVisitorId($visitorId)
    {
        $this->registeredVisitorIds[] = (string) $visitorId;
    }

    /**
     * @param array $options
     */
    public function registerOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param ParserContext $context
     */
    public function registerContext(ParserContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param NodeTraverserInterface $traverser
     *
     * @throws \GrumPHP\Exception\RuntimeException
     */
    public function configure(NodeTraverserInterface $traverser)
    {
        $this->guardTaskHasVisitors();
        $this->guardContextIsRegistered();

        $configuredVisitors = $this->normalizeConfiguredVisitors($this->options['visitors']);
        $configuredVisitorIds = array_keys($configuredVisitors);
        $visitorIds = array_values(array_intersect($this->registeredVisitorIds, $configuredVisitorIds));
        $unknownConfiguredVisitorIds = array_diff($configuredVisitorIds, $this->registeredVisitorIds);
        
        if (count($unknownConfiguredVisitorIds)) {
            throw new RuntimeException(
                sprintf('Found unknown php_parser visitors: %s', implode(',', $unknownConfiguredVisitorIds))
            );
        }

        foreach ($visitorIds as $visitorId) {
            $visitor = $this->container->get($visitorId);

            if ($visitor instanceof ContextAwareVisitorInterface) {
                $visitor->setContext($this->context);
            }

            $options = $configuredVisitors[$visitorId];
            if ($visitor instanceof ConfigurableVisitorInterface && is_array($options)) {
                $visitor->configure($options);
            }

            $traverser->addVisitor($visitor);
        }

        // Reset context to make sure the next configure call will actually run in the correct context:
        $this->context = null;
    }

    /**
     * Add the configuration prefix to a string if it is not added yet.
     * This makes it possible to use short visitor names in the configuration.
     *
     * @param array $visitorIds
     *
     * @return array
     */
    private function normalizeConfiguredVisitors($visitorIds)
    {
        $matcher = '/^' . preg_quote(self::CONFIGURATION_PREFIX, '/') . '/';
        $newVisitors = [];
        foreach ($visitorIds as $visitorId => $config) {
            $newVisitorId = preg_match($matcher, $visitorId) ? $visitorId : self::CONFIGURATION_PREFIX . $visitorId;
            $newVisitors[$newVisitorId] = $config;
        }

        return $newVisitors;
    }

    /**
     *
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
