<?php

namespace GrumPHP\Configuration\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TaskCompilerPass
 *
 * @package GrumPHP\Configuration\Compiler
 */
class TaskCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('task_runner');
        $taggedServices = $container->findTaggedServiceIds('grumphp.task');
        $configuration = $container->getParameter('tasks');

        foreach ($taggedServices as $id => $tags) {
            $configKey = $container->get('config')->locateConfigKey($tags);
            if (!array_key_exists($configKey, $configuration)) {
                continue;
            }

            $definition->addMethodCall('addTask', array(new Reference($id)));
        }
    }
}
