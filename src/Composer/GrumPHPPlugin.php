<?php

declare(strict_types=1);

namespace GrumPHP\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvents;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Console\Command\ConfigureCommand;
use GrumPHP\Console\Command\Git\DeInitCommand;
use GrumPHP\Console\Command\Git\InitCommand;
use GrumPHP\Locator\ExternalCommand;
use GrumPHP\Process\ProcessFactory;
use Symfony\Component\Process\ExecutableFinder;

class GrumPHPPlugin implements PluginInterface, EventSubscriberInterface
{
    const PACKAGE_NAME = 'phpro/grumphp';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var bool
     */
    protected $configureScheduled = false;

    /**
     * @var bool
     */
    protected $initScheduled = false;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Attach package installation events:.
     *
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'postPackageInstall',
            PackageEvents::POST_PACKAGE_UPDATE => 'postPackageUpdate',
            PackageEvents::PRE_PACKAGE_UNINSTALL => 'prePackageUninstall',
            ScriptEvents::POST_INSTALL_CMD => 'runScheduledTasks',
            ScriptEvents::POST_UPDATE_CMD => 'runScheduledTasks',
        ];
    }

    /**
     * When this package is updated, the git hook is also initialized.
     */
    public function postPackageInstall(PackageEvent $event)
    {
        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getPackage();

        if (!$this->guardIsGrumPhpPackage($package)) {
            return;
        }

        // Schedule init when command is completed
        $this->configureScheduled = true;
        $this->initScheduled = true;
    }

    /**
     * When this package is updated, the git hook is also updated.
     */
    public function postPackageUpdate(PackageEvent $event)
    {
        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getTargetPackage();

        if (!$this->guardIsGrumPhpPackage($package)) {
            return;
        }

        // Schedule init when command is completed
        $this->initScheduled = true;
    }

    /**
     * When this package is uninstalled, the generated git hooks need to be removed.
     */
    public function prePackageUninstall(PackageEvent $event)
    {
        /** @var UninstallOperation $operation */
        $operation = $event->getOperation();
        $package = $operation->getPackage();

        if (!$this->guardIsGrumPhpPackage($package)) {
            return;
        }

        // First remove the hook, before everything is deleted!
        $this->deInitGitHook();
    }

    public function runScheduledTasks(Event $event)
    {
        if ($this->initScheduled) {
            $this->runGrumPhpCommand(ConfigureCommand::COMMAND_NAME);
        }
        if ($this->initScheduled) {
            $this->initGitHook();
        }
    }

    protected function guardIsGrumPhpPackage(PackageInterface $package): bool
    {
        return self::PACKAGE_NAME === $package->getName();
    }

    /**
     * Initialize git hooks.
     */
    protected function initGitHook()
    {
        $this->runGrumPhpCommand(InitCommand::COMMAND_NAME);
    }

    /**
     * Deinitialize git hooks.
     */
    protected function deInitGitHook()
    {
        $this->runGrumPhpCommand(DeInitCommand::COMMAND_NAME);
    }

    /**
     * Run the GrumPHP console to (de)init the git hooks.
     */
    protected function runGrumPhpCommand(string $command)
    {
        $config = $this->composer->getConfig();
        $commandLocator = new ExternalCommand($config->get('bin-dir'), new ExecutableFinder());
        $executable = $commandLocator->locate('grumphp');

        $commandlineArgs = ProcessArgumentsCollection::forExecutable($executable);
        $commandlineArgs->add($command);
        $commandlineArgs->add('--no-interaction');

        $process = ProcessFactory::fromArguments($commandlineArgs);

        // Check executable which is running:
        if ($this->io->isVeryVerbose()) {
            $this->io->write('Running process : '.$process->getCommandLine());
        }

        $process->run();
        if (!$process->isSuccessful()) {
            $this->io->write(
                '<fg=red>GrumPHP can not sniff your commits. Did you specify the correct git-dir?</fg=red>'
            );
            $this->io->write('<fg=red>'.$process->getErrorOutput().'</fg=red>');

            return;
        }

        $this->io->write('<fg=yellow>'.$process->getOutput().'</fg=yellow>');
    }
}
