<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Util\Filesystem;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

final class ContainerFactory
{
    public static function buildFromConfiguration(string $path): ContainerBuilder
    {
        $container = new ContainerBuilder();

        // Add compiler passes:
        $container->addCompilerPass(new Compiler\ExtensionCompilerPass());
        $container->addCompilerPass(new Compiler\PhpParserCompilerPass());
        $container->addCompilerPass(new Compiler\TaskCompilerPass());
        $container->addCompilerPass(new Compiler\TestSuiteCompilerPass());
        $container->addCompilerPass(
            new RegisterListenersPass('event_dispatcher', 'grumphp.event_listener', 'grumphp.event_subscriber')
        );

        // Load basic service file + custom user configuration
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../resources/config'));
        $loader->load('formatter.yml');
        $loader->load('linters.yml');
        $loader->load('parameters.yml');
        $loader->load('parsers.yml');
        $loader->load('services.yml');
        $loader->load('subscribers.yml');
        $loader->load('tasks.yml');
        $loader->load('util.yml');

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
