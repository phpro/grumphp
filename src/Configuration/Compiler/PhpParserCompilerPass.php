<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Compiler;

use GrumPHP\Parser\Php\Container\VisitorContainer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PhpParserCompilerPass implements CompilerPassInterface
{
    const TAG = 'php_parser.visitor';

    /**
     * Sets the visitors as non shared services.
     * This will make sure that the state of the visitor won't need to be reset after an iteration of the traverser.
     *
     * All visitor Ids are registered in the traverser configurator.
     * The configurator will be used to apply the configured visitors to the traverser.
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process(ContainerBuilder $container): void
    {
        $traverserConfigurator = $container->findDefinition('grumphp.parser.php.configurator.traverser');
        $visitorContainer = $container->findDefinition(VisitorContainer::class);
        $instances = [];
        foreach ($container->findTaggedServiceIds(self::TAG) as $id => $tags) {
            // Build a list of visitor instances that will be made available:
            $definition = $container->findDefinition($id);
            $definition->setShared(false);
            $instances[$id] = new Reference($id);

            // Register all specified aliases to the traverser configurator:
            foreach ($tags as $tag) {
                $alias = array_key_exists('alias', $tag) ? $tag['alias'] : $id;
                $traverserConfigurator->addMethodCall('registerVisitorId', [$alias, $id]);
            }
        }

        // Register instances to the visitor container:
        $visitorContainer->replaceArgument('$instances', $instances);
    }
}
