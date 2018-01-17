<?php

namespace GrumPHP\Console;

use GrumPHP\Configuration\ContainerFactory;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\IO\IOInterface;
use GrumPHP\Locator\ConfigurationFile;
use GrumPHP\Util\Composer;
use GrumPHP\Util\Filesystem;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Application extends SymfonyConsole
{
    const APP_NAME = 'GrumPHP';
    const APP_VERSION = '0.13.1';

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var string
     */
    protected $configDefaultPath;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Helper\ComposerHelper
     */
    protected $composerHelper;

    /**
     * Set up application:
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->container = $this->getContainer();
        $this->setDispatcher($this->container->get('event_dispatcher'));

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
                $this->getDefaultConfigPath()
            )
        );

        return $definition;
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
            $container->get('grumphp.util.filesystem'),
            $container->get('git.repository')
        );
        $commands[] = new Command\RunCommand(
            $container->get('config'),
            $container->get('locator.registered_files')
        );

        $commands[] = new Command\Git\CommitMsgCommand(
            $container->get('config'),
            $container->get('locator.changed_files'),
            $container->get('grumphp.util.filesystem')
        );
        $commands[] = new Command\Git\DeInitCommand(
            $container->get('config'),
            $container->get('grumphp.util.filesystem')
        );
        $commands[] = new Command\Git\InitCommand(
            $container->get('config'),
            $container->get('grumphp.util.filesystem'),
            $container->get('process_builder')
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
        $helperSet->set($this->initializeComposerHelper());
        $helperSet->set(new Helper\PathsHelper(
            $container->get('config'),
            $container->get('grumphp.util.filesystem'),
            $container->get('locator.external_command'),
            $this->getDefaultConfigPath()
        ));
        $helperSet->set(new Helper\TaskRunnerHelper(
            $container->get('config'),
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
        $configPath = $input->getParameterOption(['--config', '-c'], $this->getDefaultConfigPath());

        // Build the service container:
        $this->container = ContainerFactory::buildFromConfiguration($configPath);

        return $this->container;
    }

    /**
     * Configure IO of GrumPHP objects
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output);

        $container = $this->getContainer();

        // Register the console input and output to the container
        $container->set('console.input', $input);
        $container->set('console.output', $output);

        // Redirect the GrumPHP logger to the stdout in verbose mode
        /** @var IOInterface $io */
        $io = $container->get('grumphp.io.console');
        if ($io->isVerbose()) {
            /** @var Logger $logger */
            $logger = $container->get('grumphp.logger');
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        }
    }

    /**
     * @return string
     */
    protected function getDefaultConfigPath()
    {
        if ($this->configDefaultPath) {
            return $this->configDefaultPath;
        }

        $locator = new ConfigurationFile($this->filesystem);
        $this->configDefaultPath = $locator->locate(
            getcwd(),
            $this->initializeComposerHelper()->getRootPackage()
        );

        return $this->configDefaultPath;
    }

    /**
     * @return Helper\ComposerHelper
     */
    protected function initializeComposerHelper()
    {
        if ($this->composerHelper) {
            return $this->composerHelper;
        }

        try {
            $composerFile = getcwd() . DIRECTORY_SEPARATOR . 'composer.json';
            $configuration = Composer::loadConfiguration();
            Composer::ensureProjectBinDirInSystemPath($configuration->get('bin-dir'));
            $rootPackage = Composer::loadRootPackageFromJson($composerFile, $configuration);
        } catch (RuntimeException $e) {
            $configuration = null;
            $rootPackage = null;
        }

        return $this->composerHelper = new Helper\ComposerHelper($configuration, $rootPackage);
    }
}
