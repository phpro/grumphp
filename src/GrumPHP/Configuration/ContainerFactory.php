<?php

namespace GrumPHP\Configuration;

use GrumPHP\Configuration\Compiler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\Filesystem\Filesystem;

final class ContainerFactory
{
    /**
     * @param string $path path to grumphp.yml
     *
     * @return ContainerBuilder
     */
    public static function buildFromConfiguration($path)
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new Compiler\ExtensionCompilerPass());
        $container->addCompilerPass(new Compiler\TaskCompilerPass());
        $container->addCompilerPass(
            new RegisterListenersPass('event_dispatcher', 'grumphp.event_listener', 'grumphp.event_subscriber')
        );

        // Load basic service file + custom user configuration
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../../resources/config'));
        $loader->load('linters.yml');
        $loader->load('parameters.yml');
        $loader->load('services.yml');
        $loader->load('tasks.yml');

        // Load grumphp.yml file:
        $filesystem = new Filesystem();
        if ($filesystem->exists($path)) {
            $loader->load($path);
        }

        // Compile configuration to make sure that tasks are added to the taskrunner
        $container->compile();

        return $container;
    }
}
