<?php

namespace GrumPHP\Configuration\Compiler;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskCompilerPass implements CompilerPassInterface
{
    const TAG_GRUMPHP_TASK = 'grumphp.task';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('task_runner');
        $taggedServices = $container->findTaggedServiceIds(self::TAG_GRUMPHP_TASK);
        $configuration = $container->getParameter('tasks');

        $tasksRegistered = [];
        $tasksMetadata = [];
        $tasksConfiguration = [];
        foreach ($taggedServices as $id => $tags) {
            $taskTag = $this->getTaskTag($tags);
            $configKey = $taskTag['config'];
            if (in_array($configKey, $tasksRegistered)) {
                throw new RuntimeException(
                    sprintf('The name of a task should be unique. Duplicate found: %s', $configKey)
                );
            }

            $tasksRegistered[] = $configKey;
            if (!array_key_exists($configKey, $configuration)) {
                continue;
            }

            // Load configuration and metadata:
            $taskConfig = is_array($configuration[$configKey]) ? $configuration[$configKey] : [];
            $tasksMetadata[$configKey] = $this->parseTaskMetadata($taskConfig);

            // The metadata can't be part of the actual configuration.
            // This will throw exceptions during options resolving.
            unset($taskConfig['metadata']);
            $tasksConfiguration[$configKey] = $taskConfig;

            // Add the task to the task runner:
            $definition->addMethodCall('addTask', [new Reference($id)]);
        }

        sort($tasksRegistered);

        $container->setParameter('grumphp.tasks.registered', $tasksRegistered);
        $container->setParameter('grumphp.tasks.configuration', $tasksConfiguration);
        $container->setParameter('grumphp.tasks.metadata', $tasksMetadata);
    }

    /**
     * @param array $tags
     *
     * @return array
     */
    private function getTaskTag(array $tags)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['config']);

        return $resolver->resolve(current($tags));
    }

    /**
     * @param $configuration
     *
     * @return array
     */
    private function parseTaskMetadata($configuration)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'priority' => 0,
            'blocking' => true,
        ]);

        $metadata = isset($configuration['metadata']) ? $configuration['metadata'] : [];

        return $resolver->resolve($metadata);
    }
}
