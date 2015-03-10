<?php

namespace GrumPHP\Configuration;

use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

final class ContainerFactory
{
    /**
     * Configuration defaults
     *
     * @var array
     */
    private static $defaults = array(
        'base_dir' => '.',
        'bin_dir' => './vendor/bin',
        'git_dir' => '.',
    );

    /**
     * @param string $path path to grumphp.yml
     *
     * @return ContainerBuilder
     */
    public static function buildFromConfiguration($path)
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($path)) {
            throw new RuntimeException(sprintf('The configuration file could not be found at "%s".', $path));
        }

        return self::buildFromOptions(Yaml::parse($path));
    }

    /**
     * @param array $options
     *
     * @return ContainerBuilder
     */
    public static function buildFromOptions(array $options)
    {
        $options = array_merge(self::$defaults, $options);

        $container = new ContainerBuilder();

        $loader = new YamlFileLoader($container, new FileLocator($options['base_dir'] . '/resources/config'));
        $loader->load('services.yml');

        $container->setParameter('base_dir', $options['base_dir']);
        $container->setParameter('bin_dir', $options['bin_dir']);
        $container->setParameter('git_dir', $options['git_dir']);
        $container->setParameter('phpcs.standard', $options['phpcs']['standard']);

        return $container;
    }
}
