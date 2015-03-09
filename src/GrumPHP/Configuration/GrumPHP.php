<?php

namespace GrumPHP\Configuration;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GrumPHP
 *
 * @package GrumPHP\Configuration
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
     * @return bool
     */
    public function hasPhpcs()
    {
        return $this->container->has('phpcs');
    }

    /**
     * @return Phpcs
     */
    public function getPhpcs()
    {
        return $this->container->get('phpcs');
    }
}
