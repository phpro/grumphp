<?php

namespace GrumPHP\Configuration;

use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GrumPHP
 *
 * @package GrumPHP\Configuration
 */
class GrumPHP
{

    const CONFIG_NAMESPACE = 'grumphp';

    /**
     * @var Phpcs
     */
    protected $phpcs;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Configuration defaults
     *
     * @var array
     */
    private $defaults = array(
        'base_dir' => '.',
        'bin_dir' => './vendor/bin',
        'git_dir' => '.',
    );

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options)
    {
        // TODO: there should probably be some validation here...

        $this->buildContainer(array_merge($this->defaults, $options));
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

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param string $path path to grumphp.yml
     *
     * @return GrumPHP
     */
    public static function loadFromConfig($path)
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($path)) {
            throw new RuntimeException(sprintf('The configuration file could not be found at "%s".', $path));
        }

        return new self(Yaml::parse($path));
    }

    /**
     * Build the DI container for GrumPHP.
     *
     * @todo move this to an internal config file somehow?
     *
     * @param array $options
     */
    private function buildContainer(array $options)
    {
        $this->container = new ContainerBuilder();

        $this->container->setParameter('base_dir', $options['base_dir']);
        $this->container->setParameter('bin_dir', $options['bin_dir']);
        $this->container->setParameter('git_dir', $options['git_dir']);
        $this->container->setParameter('phpcs.standard', $options['phpcs']['standard']);

        $this->container->register('phpcs', 'GrumPHP\Configuration\Phpcs')
            ->addArgument(array('standard' => '%phpcs.standard%')); // TODO: change this strangeness when Phpcs doesn't extend AbstractOptions anymore

        $this->container->register('filesystem', 'Symfony\Component\Filesystem\Filesystem');
    }

}
