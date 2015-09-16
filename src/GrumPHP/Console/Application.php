<?php

namespace GrumPHP\Console;

use GrumPHP\Configuration\ContainerFactory;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

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
     * @var ContainerBuilder
     */
    protected $container;

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
                'Path to config',
                $this->getConfigDefaultPath()
            )
        );

        return $definition;
    }

    /**
     * @return string
     */
    protected function getConfigDefaultPath()
    {
        $defaultConfigName = getcwd() . DIRECTORY_SEPARATOR . self::APP_CONFIG_FILE;
        $composerFile = 'composer.json';

        if (!file_exists($composerFile)) {
            return $defaultConfigName;
        }

        $composer = json_decode(file_get_contents($composerFile), true);
        if (isset($composer['extra']['grumphp']['config-default-path'])) {
            return $composer['extra']['grumphp']['config-default-path'];
        }

        return $defaultConfigName;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $container = $this->getContainer();
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\ConfigureCommand(
            $container->get('config'),
            $container->get('filesystem'),
            $container->get('git.repository'),
            $container->get('task_runner')
        );

        $commands[] = new Command\Git\DeInitCommand(
            $container->get('config'),
            $container->get('filesystem')
        );
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

    protected function getDefaultHelperSet()
    {
        $container = $this->getContainer();

        $helperSet = parent::getDefaultHelperSet();
        $helperSet->set(new Helper\PathsHelper(
            $container->get('config'),
            $container->get('filesystem')
        ));
        $helperSet->set(new Helper\TaskRunnerHelper(
            $container->get('task_runner')
        ));

        return $helperSet;
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function getContainer()
    {
        if ($this->container) {
            return $this->container;
        }

        // Load cli options:
        $input = new ArgvInput();
        $input->bind($this->getDefaultInputDefinition());
        $configPath = $input->getOption('config');

        // Make sure to set the full path when it is declared relative
        // This will fix some issues in windows.
        $filesystem = new Filesystem();
        if (!$filesystem->isAbsolutePath($configPath)) {
            $configPath = getcwd() . DIRECTORY_SEPARATOR . $configPath;
        }

        // Build the service container:
        $this->container = ContainerFactory::buildFromConfiguration($configPath);

        return $this->container;
    }
}
