<?php

namespace GrumPHP\Configuration\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PhpParserCompilerPass
 *
 * @package GrumPHP\Configuration\Compiler
 */
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
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $traverserConfigurator = $container->findDefinition('grumphp.parser.php.configurator.traverser');
        foreach ($container->findTaggedServiceIds(self::TAG) as $id => $tags) {
            $container->findDefinition($id)->setShared(false);
            foreach ($tags as $tag) {
                $alias = array_key_exists('alias', $tag) ? $tag['alias'] : $id;
                $traverserConfigurator->addMethodCall('registerVisitorId', array($alias, $id));
            }
        }
    }
}
