<?php

declare(strict_types=1);

namespace GrumPHP\Console;

use GrumPHP\Configuration\ContainerFactory;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\IO\ConsoleIO;
use GrumPHP\Locator\ConfigurationFile;
use GrumPHP\Util\Composer;
use GrumPHP\Util\Filesystem;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Application extends SymfonyConsole
{
    const APP_NAME = 'GrumPHP';
    const APP_VERSION = '0.15.0';

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

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->container = $this->getContainer();
        $this->setDispatcher($this->container->get('event_dispatcher'));

        parent::__construct(self::APP_NAME, self::APP_VERSION);
    }

    protected function getDefaultInputDefinition(): InputDefinition
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

    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\ConfigureCommand(
            $this->container->get('config'),
            $this->container->get('grumphp.util.filesystem'),
            $this->container->get('git.repository')
        );
        $commands[] = new Command\RunCommand(
            $this->container->get('config'),
            $this->container->get('locator.registered_files')
        );

        $commands[] = new Command\Git\CommitMsgCommand(
            $this->container->get('config'),
            $this->container->get('locator.changed_files'),
            $this->container->get('grumphp.util.filesystem')
        );
        $commands[] = new Command\Git\DeInitCommand(
            $this->container->get('config'),
            $this->container->get('grumphp.util.filesystem')
        );
        $commands[] = new Command\Git\InitCommand(
            $this->container->get('config'),
            $this->container->get('grumphp.util.filesystem'),
            $this->container->get('process_builder')
        );
        $commands[] = new Command\Git\PreCommitCommand(
            $this->container->get('config'),
            $this->container->get('locator.changed_files')
        );

        return $commands;
    }

    protected function getDefaultHelperSet(): HelperSet
    {
        $helperSet = parent::getDefaultHelperSet();
        $helperSet->set($this->initializeComposerHelper());
        $helperSet->set(new Helper\PathsHelper(
            $this->container->get('config'),
            $this->container->get('grumphp.util.filesystem'),
            $this->container->get('locator.external_command'),
            $this->getDefaultConfigPath()
        ));
        $helperSet->set(new Helper\TaskRunnerHelper(
            $this->container->get('config'),
            $this->container->get('task_runner'),
            $this->container->get('event_dispatcher')
        ));

        return $helperSet;
    }

    protected function getContainer(): ContainerBuilder
    {
        if ($this->container) {
            return $this->container;
        }

        // Load cli options:
        $input = new ArgvInput();
        $configPath = $input->getParameterOption(['--config', '-c'], $this->getDefaultConfigPath());
        $configPath = $this->updateUserConfigPath($configPath);
        $output = new ConsoleOutput();

        // Build the service container:
        $this->container = ContainerFactory::buildFromConfiguration($configPath);
        $this->container->set('console.input', $input);
        $this->container->set('console.output', $output);

        return $this->container;
    }

    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output);

        /** @var ConsoleIO $io */
        $io = $this->container->get('grumphp.io.console');

        // Redirect the GrumPHP logger to the stdout in verbose mode
        if ($io->isVerbose()) {
            /** @var Logger $logger */
            $logger = $this->container->get('grumphp.logger');
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        }
    }

    protected function getDefaultConfigPath(): string
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

    protected function initializeComposerHelper(): Helper\ComposerHelper
    {
        if ($this->composerHelper) {
            return $this->composerHelper;
        }

        try {
            $composerFile = getcwd().DIRECTORY_SEPARATOR.'composer.json';
            $configuration = Composer::loadConfiguration();
            Composer::ensureProjectBinDirInSystemPath($configuration->get('bin-dir'));
            $rootPackage = Composer::loadRootPackageFromJson($composerFile, $configuration);
        } catch (RuntimeException $e) {
            $configuration = null;
            $rootPackage = null;
        }

        return $this->composerHelper = new Helper\ComposerHelper($configuration, $rootPackage);
    }

    /**
     * Prefixes the cwd to the path given by the user.
     */
    private function updateUserConfigPath(string $configPath): string
    {
        if ($configPath !== $this->getDefaultConfigPath()) {
            $configPath = getcwd().DIRECTORY_SEPARATOR.$configPath;
        }

        return $configPath;
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        /** @var ConsoleIO $io */
        $io = $this->container->get('grumphp.io.console');

        return parent::run($io->getInput(), $io->getOutput());
    }
}
