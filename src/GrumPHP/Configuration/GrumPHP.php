<?php

namespace GrumPHP\Configuration;

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
     * Get defined tasks
     *
     * @return array
     */
    public function getTasks()
    {
        $tasks = array();
        $tags = $this->container->findTaggedServiceIds('grumphp.task');

        foreach ($tags as $id => $tags) {
            $tasks[] = $this->locateConfigKey($tags);
        }

        return $tasks;
    }

    /**
     * Check config key is set
     *
     * @param $tags
     * @return null|array
     */
    public function locateConfigKey($tags)
    {
        foreach ($tags as $data) {
            if (isset($data['config'])) {
                return $data['config'];
            }
        }

        return null;
    }

    /**
     * @param string|null $taskName
     *
     * @return array
     */
    public function getTaskConfig($taskName = null)
    {
        $tasksConfig = $this->container->getParameter('tasks');
        if (!$taskName) {
            return $tasksConfig;
        }

        if (!array_key_exists($taskName, $tasksConfig)) {
            return array();
        }

        return $tasksConfig[$taskName];
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
}
