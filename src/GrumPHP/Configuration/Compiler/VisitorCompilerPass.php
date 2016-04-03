<?php

namespace GrumPHP\Configuration\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class VisitorCompilerPass
 *
 * @package GrumPHP\Configuration\Compiler
 */
class VisitorCompilerPass implements CompilerPassInterface
{
    const TAG_PHPPARSER_VISITOR = 'php_parser.visitor';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('parser.phpparser');
        $taggedServices = $container->findTaggedServiceIds(self::TAG_PHPPARSER_VISITOR);

        foreach (array_keys($taggedServices) as $id) {
            // Add the node visitor
            $definition->addMethodCall('addNodeVisitor', array(new Reference($id)));
        }
    }
}
