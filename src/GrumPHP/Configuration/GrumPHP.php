<?php

namespace GrumPHP\Configuration;

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

    /**
     * {@inheritdoc}
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getBinDir()
    {
        return $this->container->getParameter('bin_dir');
    }

    /**
     * @return string
     */
    public function getGitDir()
    {
        return $this->container->getParameter('git_dir');
    }

    /**
     * @return bool
     */
    public function stopOnFailure()
    {
        return (bool) $this->container->getParameter('stop_on_failure');
    }

    /**
     * @return bool
     */
    public function ignoreUnstagedChanges()
    {
        return (bool) $this->container->getParameter('ignore_unstaged_changes');
    }

    /**
     * @return array
     */
    public function getRegisteredTasks()
    {
        return $this->container->getParameter('grumphp.tasks.registered');
    }

    /**
     * @param string $taskName
     *
     * @return array
     */
    public function getTaskConfiguration($taskName)
    {
        $tasksConfiguration = $this->container->getParameter('grumphp.tasks.configuration');
        if (!array_key_exists($taskName, $tasksConfiguration)) {
            throw new RuntimeException('Could not find task configuration. Invalid task: ' . $taskName);
        }

        return $tasksConfiguration[$taskName];
    }

    /**
     * @param $taskName
     *
     * @return array
     */
    public function getTaskMetadata($taskName)
    {
        $tasksMetadata = $this->container->getParameter('grumphp.tasks.metadata');
        if (!array_key_exists($taskName, $tasksMetadata)) {
            throw new RuntimeException('Could not find task metadata. Invalid task: ' . $taskName);
        }

        return $tasksMetadata[$taskName];
    }

    /**
     * Get ascii content path from grumphp.yml file
     *
     * @param $resource
     *
     * @return string|null
     */
    public function getAsciiContentPath($resource)
    {
        $paths = $this->container->getParameter('ascii');
        if (!array_key_exists($resource, $paths)) {
            return null;
        }

        return $paths[$resource];
    }

    /**
     * @param string $taskName
     * @return bool
     */
    public function isBlockingTask($taskName)
    {
        $taskMetadata = $this->getTaskMetadata($taskName);
        return $taskMetadata['blocking'];
    }
}
