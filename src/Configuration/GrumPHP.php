<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Collection\TestSuiteCollection;
use GrumPHP\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The code representation of our grumphp.yml file.
 */
class GrumPHP
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getBinDir(): string
    {
        return $this->container->getParameter('bin_dir');
    }

    public function getGitDir(): string
    {
        return $this->container->getParameter('git_dir');
    }

    /**
     * @return string|null
     */
    public function getHooksDir()
    {
        return $this->container->getParameter('hooks_dir');
    }

    public function getHooksPreset(): string
    {
        return $this->container->getParameter('hooks_preset');
    }

    public function stopOnFailure(): bool
    {
        return (bool) $this->container->getParameter('stop_on_failure');
    }

    public function ignoreUnstagedChanges(): bool
    {
        return (bool) $this->container->getParameter('ignore_unstaged_changes');
    }

    public function getProcessAsyncLimit(): int
    {
        return (int) $this->container->getParameter('process_async_limit');
    }

    public function getProcessAsyncWaitTime(): int
    {
        return (int) $this->container->getParameter('process_async_wait');
    }

    /**
     * @return float|null
     */
    public function getProcessTimeout()
    {
        $timeout = $this->container->getParameter('process_timeout');
        if (null === $timeout) {
            return null;
        }

        return (float) $timeout;
    }

    /**
     * @return null|string
     */
    public function getAdditionalInfo()
    {
        return $this->container->getParameter('additional_info');
    }

    public function getRegisteredTasks(): array
    {
        return $this->container->getParameter('grumphp.tasks.registered');
    }

    /**
     * Gets a value indicating whether the Git commit hook circumvention tip should be shown when a task fails.
     */
    public function hideCircumventionTip(): bool
    {
        return (bool) $this->container->getParameter('hide_circumvention_tip');
    }

    public function getTaskConfiguration(string $taskName): array
    {
        $tasksConfiguration = $this->container->getParameter('grumphp.tasks.configuration');
        if (!array_key_exists($taskName, $tasksConfiguration)) {
            throw new RuntimeException('Could not find task configuration. Invalid task: '.$taskName);
        }

        return $tasksConfiguration[$taskName];
    }

    public function getTaskMetadata($taskName): array
    {
        $tasksMetadata = $this->container->getParameter('grumphp.tasks.metadata');
        if (!array_key_exists($taskName, $tasksMetadata)) {
            throw new RuntimeException('Could not find task metadata. Invalid task: '.$taskName);
        }

        return $tasksMetadata[$taskName];
    }

    public function getTestSuites(): TestSuiteCollection
    {
        return $this->container->getParameter('grumphp.testsuites');
    }

    /**
     * Get ascii content path from grumphp.yml file.
     *
     * @return string|null
     */
    public function getAsciiContentPath(string $resource)
    {
        if (null === $this->container->getParameter('ascii')) {
            return null;
        }

        $paths = $this->container->getParameter('ascii');
        if (!array_key_exists($resource, $paths)) {
            return null;
        }

        // Deal with multiple ascii files by returning one at random.
        if (\is_array($paths[$resource])) {
            shuffle($paths[$resource]);
            return reset($paths[$resource]);
        }

        return $paths[$resource];
    }

    public function isBlockingTask(string $taskName): bool
    {
        $taskMetadata = $this->getTaskMetadata($taskName);

        return $taskMetadata['blocking'];
    }
}
