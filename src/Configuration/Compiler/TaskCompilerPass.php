<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Compiler;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\Configurator\TaskConfigurator;
use GrumPHP\Configuration\Resolver\TaskConfigResolver;
use GrumPHP\Exception\TaskConfigResolverException;
use GrumPHP\Task\Config\LazyTaskConfig;
use GrumPHP\Task\Config\Metadata;
use GrumPHP\Task\Config\TaskConfig;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskCompilerPass implements CompilerPassInterface
{
    private const TAG_GRUMPHP_TASK = 'grumphp.task';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $tasksCollection = $container->findDefinition(TasksCollection::class);
        $availableTasks = $this->fetchAvailableTasksInfo($container);
        $configuredTasks = $container->getParameter('tasks') ?: [];
        $taskConfigResolver = $this->buildTaskConfigResolver($availableTasks);

        // Configure tasks
        foreach ($configuredTasks as $taskName => $config) {
            $taskConfig = $config ?? [];
            $metadata = new Metadata((array) ($taskConfig['metadata'] ?? []));
            $currentTaskName = $metadata->task() ?: $taskName;
            if (!array_key_exists($currentTaskName, $availableTasks)) {
                throw TaskConfigResolverException::unkownTask($currentTaskName);
            }

            // Determine Keys:
            $currentTaskService = $availableTasks[$currentTaskName];
            ['id' => $taskId, 'class' => $taskClass,] = $currentTaskService;
            $configuredTaskKey = $taskId.'.configured';

            // Configure task:
            $taskBuilder = new Definition($taskClass, [
                new Reference($taskId),
                new LazyTaskConfig(
                    function () use ($taskName, $taskConfigResolver, $currentTaskName, $taskConfig, $metadata) {
                        return new TaskConfig(
                            $taskName,
                            $taskConfigResolver->resolve($currentTaskName, $taskConfig),
                            $metadata
                        );
                    }
                )
            ]);
            $taskBuilder->setFactory(new Reference(TaskConfigurator::class));
            $taskBuilder->addTag('configured.task');

            // Register services:
            $container->setDefinition($configuredTaskKey, $taskBuilder);
            $tasksCollection->addMethodCall('add', [new Reference($configuredTaskKey)]);
        }

        // Register available and configured tasks for easy data usage in the application:
        $container->set(TaskConfigResolver::class, $taskConfigResolver);
        $container->setParameter('grumphp.tasks.configured', array_keys($configuredTasks));
    }

    private function getTaskTag(array $tags): array
    {
        static $taskTagResolver;
        if (null === $taskTagResolver) {
            $taskTagResolver = new OptionsResolver();
            $taskTagResolver->setRequired(['task']);
            $taskTagResolver->setAllowedTypes('task', ['string']);
        }

        return $taskTagResolver->resolve(current($tags));
    }

    private function fetchAvailableTasksInfo(ContainerBuilder $container): array
    {
        $map = [];
        $taggedServices = $container->findTaggedServiceIds(self::TAG_GRUMPHP_TASK);

        foreach ($taggedServices as $serviceId => $tags) {
            $definition = $container->findDefinition($serviceId);
            // Make sure to set shared to false so that a new instance is always returned
            $definition->setShared(false);

            $taskInfo = $this->getTaskTag($tags);
            $name = $taskInfo['task'];
            $class = $definition->getClass();

            $map[$name] = [
                'id' => $serviceId,
                'class' => $class,
                'task' => $name,
            ];
        }

        return $map;
    }

    private function buildTaskConfigResolver(array $availableTasks): TaskConfigResolver
    {
        return new TaskConfigResolver(
            array_map(
                function ($availableTask): string {
                    return (string) ($availableTask['class'] ?? '');
                },
                $availableTasks
            )
        );
    }
}
