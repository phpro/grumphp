<?php

namespace GrumPHP\Console;

use GrumPHP\Configuration\ContainerFactory;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class Application
 *
 * @package GrumPHP\Console
 */
class Application extends SymfonyConsole
{
    const APP_NAME = 'GrumPHP';
    const APP_VERSION = '0.7.0';
    const APP_CONFIG_FILE = 'grumphp.yml';

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var string
     */
    protected $configDefaultPath;

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
        if ($this->configDefaultPath) {
            return $this->configDefaultPath;
        }

        $this->configDefaultPath = getcwd() . DIRECTORY_SEPARATOR . self::APP_CONFIG_FILE;
        $composerFile = 'composer.json';

        if (!file_exists($composerFile)) {
            return $this->configDefaultPath;
        }

        $composer = json_decode(file_get_contents($composerFile), true);
        if (isset($composer['extra']['grumphp']['config-default-path'])) {
            $this->configDefaultPath = $composer['extra']['grumphp']['config-default-path'];
        }

        return $this->configDefaultPath;
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
        $commands[] = new Command\RunCommand(
            $container->get('config'),
            $container->get('locator.registered_files')
        );

        $commands[] = new Command\Git\CommitMsgCommand(
            $container->get('config'),
            $container->get('locator.changed_files')
        );
        $commands[] = new Command\Git\DeInitCommand(
            $container->get('config'),
            $container->get('filesystem')
        );
        $commands[] = new Command\Git\InitCommand(
            $container->get('config'),
            $container->get('filesystem'),
            ProcessBuilder::create()
        );
        $commands[] = new Command\Git\PreCommitCommand(
            $container->get('config'),
            $container->get('locator.changed_files')
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
            $container->get('task_runner'),
            $container->get('event_dispatcher')
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
        $configPath = $input->getParameterOption(array('--config', '-c'), $this->getConfigDefaultPath());

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
