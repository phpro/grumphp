<?php

declare(strict_types=1);

namespace GrumPHP\Configuration\Resolver;

use GrumPHP\Exception\TaskConfigResolverException;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\TaskInterface;

class TaskConfigResolver
{
    /**
     * @var array<string, string>
     */
    private $taskMap;

    public function __construct(array $taskMap)
    {
        $this->taskMap = $taskMap;
    }

    /**
     * @return array<string>
     */
    public function listAvailableTaskNames(): array
    {
        return array_keys($this->taskMap);
    }

    public function resolve(string $taskName, array $config): array
    {
        $resolver = $this->fetchByName($taskName);

        // Make sure metadata is never a part of the task configuration
        unset($config['metadata']);

        return $resolver->resolve($config);
    }

    public function fetchByName(string $taskName): ConfigOptionsResolver
    {
        if (!array_key_exists($taskName, $this->taskMap)) {
            throw TaskConfigResolverException::unknownTask($taskName);
        }

        $class = $this->taskMap[$taskName];
        if (!class_exists($class) || !is_subclass_of($class, TaskInterface::class)) {
            throw TaskConfigResolverException::unknownClass($class);
        }

        return $class::getConfigurableOptions();
    }
}
