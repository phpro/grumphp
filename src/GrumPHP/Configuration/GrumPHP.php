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
    public function getBaseDir()
    {
        return $this->container->getParameter('base_dir');
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
     * @return array
     */
    public function getActiveTasks()
    {
        return $this->container->getParameter('active_tasks');
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConfiguration($name)
    {
        return $this->container->has($name);
    }

    /**
     * @param string $name
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration($name)
    {
        return $this->container->get($name);
    }
}
