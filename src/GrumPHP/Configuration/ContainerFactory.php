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

        $container = new ContainerBuilder();
        $container->addCompilerPass(new TaskCompilerPass());

        // Load basic service file + custom user configuration
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../../resources/config'));
        $loader->load('services.yml');
        $loader->load($path);

        // Compile configuration to make sure that tasks are added to the taskrunner
        $container->compile();

        return $container;
    }
}
