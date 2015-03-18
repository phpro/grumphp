<?php

namespace GrumPHP\Console;

use GrumPHP\Configuration\ContainerFactory;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Application
 *
 * @package GrumPHP\Console
 */
class Application extends SymfonyConsole
{
    const APP_NAME = 'GrumPHP';
    const APP_VERSION = '0.1.0';
    const APP_CONFIG_FILE = 'grumphp.yml';

    /**
     * Set up application:
     */
    public function __construct()
    {
        parent::__construct(self::APP_NAME, self::APP_VERSION);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(
            new InputOption(
                'config',
                'c',
                InputOption::VALUE_OPTIONAL,
                '.',
                getcwd() . DIRECTORY_SEPARATOR . self::APP_CONFIG_FILE
            )
        );
        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $container = $this->createContainer();
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\Git\InitCommand(
            $container->get('config'),
            $container->get('filesystem'),
            $container->get('process_builder')
        );
        $commands[] = new Command\Git\PreCommitCommand(
            $container->get('config'),
            $container->get('task_runner'),
            $container->get('locator.changed_files'),
            $container->get('locator.external_command'),
            $container->get('process_builder')
        );

        return $commands;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function createContainer()
    {
        $input = new ArgvInput();
        $input->bind($this->getDefaultInputDefinition());
        $configPath = $input->getOption('config');

        $container = ContainerFactory::buildFromConfiguration($configPath);
        return $container;
    }
}
