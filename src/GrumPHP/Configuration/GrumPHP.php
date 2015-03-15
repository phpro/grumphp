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
     * @param string|null $taskName
     *
     * @return array
     */
    public function getTaskConfig($taskName = null)
    {
        if (!$taskName) {
            return $this->container->getParameter('tasks');
        }
        return $this->container->getParameter('tasks.' . $taskName);
    }

}
