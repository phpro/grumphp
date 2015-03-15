<?php

namespace GrumPHP\Configuration;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class TaskCompilerPass
 *
 * @package GrumPHP\Configuration
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

            $configKey = $this->locateCommandKey($tags);
            if (!array_key_exists($configKey, $configuration)) {
                continue;
            }

            $definition->addMethodCall('addTask', array(new Reference($id)));
        }
    }

    /**
     * @param $tags
     *
     * @return null|string
     */
    protected function locateCommandKey($tags)
    {
        foreach ($tags as $data) {
            if (isset($data['command'])) {
                return $data['command'];
            }
        }
        return null;
    }
}