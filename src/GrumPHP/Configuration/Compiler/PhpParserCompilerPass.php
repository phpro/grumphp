<?php

namespace GrumPHP\Configuration\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PhpParserCompilerPass
 *
 * @package GrumPHP\Configuration\Compiler
 */
class PhpParserCompilerPass implements CompilerPassInterface
{
    const TAG = 'php_parser.visitor';

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $traverserFactory = $container->findDefinition('grumphp.parser.php.factory.traverser');
        $taggedServices = $container->findTaggedServiceIds('php_parser.visitor');

        foreach ($taggedServices as $id => $tags) {
            // Make sure to start with a fresh state on every parse:
            $container->findDefinition($id)->setShared(false);

            // Add the node visitor to the traverser factory:
            $traverserFactory->addMethodCall('addNodeVisitor', array(new Reference($id)));
        }
    }
}
