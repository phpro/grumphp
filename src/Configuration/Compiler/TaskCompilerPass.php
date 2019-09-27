<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Compiler;

use GrumPHP\Configuration\Factory\ConfiguredTaskFactory;
use GrumPHP\Task\Config\TaskConfig;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskCompilerPass implements CompilerPassInterface
{
    const TAG_GRUMPHP_TASK = 'grumphp.task';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $taskRunner = $container->findDefinition('task_runner');
        $taggedServices = $container->findTaggedServiceIds(self::TAG_GRUMPHP_TASK);
        $configuredTasks = $container->getParameter('tasks') ?: [];

        // Always use a new task instance per dependency.
        foreach ($taggedServices as $key => $tags) {
            $container->findDefinition($key)->setShared(false);
        }

        // Configure tasks
        foreach ($configuredTasks as $taskName => $config) {
            $taskConfig = $config ?? [];
            $metadata = $this->parseTaskMetadata($taskConfig);
            $selectedTask = $metadata['task'] ?: $taskName;
            $taskKey = 'task.'.$selectedTask; // TODO : use task from tag
            if (!array_key_exists($taskKey, $taggedServices)) {
                throw new \RuntimeException('TODO : not valid service key: '.$taskKey);
            }

            // Determine Keys:
            $configuratorKey = 'task.configurator.'.$taskName;
            $configuredTaskKey = 'task.configured.'.$taskName;

            $task = $container->findDefinition($taskKey);
            // Build and configure the task
            $configureCurrentTask = new Definition(ConfiguredTaskFactory::class, [
                new TaskConfig(
                    $taskName,
                    $this->parseTaskConfig($task->getClass(), $taskConfig),
                    $metadata
                )
            ]);
            $taskBuilder = new Definition($task->getClass(), [new Reference($taskKey)]);
            $taskBuilder->setFactory(new Reference($configuratorKey));
            $taskBuilder->addTag('configured.task');

            // Register services:
            $container->setDefinition($configuratorKey, $configureCurrentTask);
            $container->setDefinition($configuredTaskKey, $taskBuilder);

            // TODO : replace by valid structure:
            $taskRunner->addMethodCall('addTask', [new Reference($configuredTaskKey)]);
        }

        // TODO : get rid of this:
        $container->setParameter('grumphp.tasks.registered', array_keys($configuredTasks));
        $container->setParameter('grumphp.tasks.configuration', $configuredTasks);
        $container->setParameter('grumphp.tasks.metadata', array_map(
            function (?array $value): array {
                return $this->parseTaskMetadata($value ?? []);
            },
            $configuredTasks
        ));
    }

    private function getTaskTag(array $tags): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['task']);

        return $resolver->resolve(current($tags));
    }

    /**
     * TODO : cleanup
     */
    private function parseTaskConfig(string $taskClass, array $config): array
    {
        if (!class_exists($taskClass) || !is_subclass_of($taskClass, TaskInterface::class)) {
            throw new \RuntimeException('TODO : Not a valid task');
        }

        $resolver = $taskClass::getConfigurableOptions();

        unset($config['metadata']);

        return $resolver->resolve($config);
    }

    private function parseTaskMetadata(array $configuration): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'priority' => 0,
            'blocking' => true,
            'task' => '',
            'label' => '',
        ]);

        $metadata = $configuration['metadata'] ?? [];

        return $resolver->resolve($metadata);
    }
}
