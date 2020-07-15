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
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Options;
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
            $metadataConfig = (array) ($taskConfig['metadata'] ?? []);
            $currentTaskName = ((string) ($metadataConfig['task'] ?? '')) ?: $taskName;
            if (!array_key_exists($currentTaskName, $availableTasks)) {
                throw TaskConfigResolverException::unknownTask($currentTaskName);
            }

            // Determine Keys:
            $currentTaskService = $availableTasks[$currentTaskName];
            ['id' => $taskId, 'class' => $taskClass, 'info' => $taskInfo] = $currentTaskService;
            $configuredTaskKey = $taskId.'.'.$taskName.'.configured';

            // Setup metadata:
            $metadata = new Metadata(array_merge(
                ['priority' => $taskInfo['priority']],
                $metadataConfig
            ));

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
            $taskBuilder->setFactory([new Reference(TaskConfigurator::class), '__invoke']);
            $taskBuilder->addTag('configured.task');

            // Register services:
            $container->setDefinition($configuredTaskKey, $taskBuilder);
            $tasksCollection->addMethodCall('add', [new Reference($configuredTaskKey)]);
        }

        // Register available and configured tasks for easy data usage in the application:
        $container->set(TaskConfigResolver::class, $taskConfigResolver);
        $container->setParameter('grumphp.tasks.configured', array_keys($configuredTasks));
    }

    private function getTaskTag(array $tag): array
    {
        static $taskTagResolver;
        if (null === $taskTagResolver) {
            $taskTagResolver = new OptionsResolver();

            // Instead of required task param : use this to enable the fallback for the deprecated tasks.
            $taskTagResolver->setDefined(['task', 'aliasFor', 'priority']);
            $taskTagResolver->setAllowedTypes('task', ['string']);
            $taskTagResolver->setAllowedTypes('aliasFor', ['string', 'null']);
            $taskTagResolver->setAllowedTypes('priority', ['int']);
            $taskTagResolver->setDefault('task', '');
            $taskTagResolver->setDefault('priority', 0);
            $taskTagResolver->setNormalizer('task', static function (Options $options, ?string $value) {
                if (!$value && !$options->offsetExists('config')) {
                    throw new MissingOptionsException('The required option "task" is missing.');
                }

                return $value;
            });

            // Clean fallback for installation with old tasks.
            $taskTagResolver->setDefined('config');
            $taskTagResolver->setNormalizer(
                'config',
                static function (Options $options, string $value) {
                    throw TaskConfigResolverException::deprectatedTask($value);
                }
            );
        }

        return $taskTagResolver->resolve($tag);
    }

    private function fetchAvailableTasksInfo(ContainerBuilder $container): array
    {
        $map = [];
        $taggedServices = $container->findTaggedServiceIds(self::TAG_GRUMPHP_TASK);

        foreach ($taggedServices as $serviceId => $tags) {
            $definition = $container->findDefinition($serviceId);
            // Make sure to set shared to false so that a new instance is always returned
            $definition->setShared(false);

            foreach ($tags as $tag) {
                $taskInfo = $this->getTaskTag($tag);
                $name = $taskInfo['task'];
                $class = $definition->getClass();

                $map[$name] = [
                    'id' => $serviceId,
                    'class' => $class,
                    'task' => $name,
                    'info' => $taskInfo,
                ];
            }
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
