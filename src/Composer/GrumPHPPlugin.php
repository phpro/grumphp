<?php

declare(strict_types=1);

namespace GrumPHP\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\InstallerEvent;
use Composer\Installer\InstallerEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class GrumPHPPlugin implements PluginInterface, EventSubscriberInterface
{
    private const PACKAGE_NAME = 'phpro/grumphp';
    private const APP_NAME = 'grumphp';
    private const COMMAND_CONFIGURE = 'configure';
    private const COMMAND_INIT = 'git:init';
    private const COMMAND_DEINIT = 'git:deinit';

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
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;

        $this->fixBrokenComposerPluginUpdate();
    }

    /**
     * Attach package installation events:.
     *
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DEPENDENCIES_SOLVING => 'detectGrumphpActions',
            ScriptEvents::POST_INSTALL_CMD => 'runScheduledTasks',
            ScriptEvents::POST_UPDATE_CMD => 'runScheduledTasks',
        ];
    }

    public function detectGrumphpActions(InstallerEvent $event): void
    {
        if (!$this->guardPluginIsEnabled()) {
            $this->io->write('GRUMPHP Plugin is disabled...');
            return;
        }

        $shouldRemove = false;
        foreach ($this->detectGrumphpOperations($event->getOperations()) as $operation) {
            switch (true) {
                case $operation instanceof UpdateOperation:
                    $this->io->write('<fg=yellow>Scheduled git:init</fg=yellow>');
                    $this->initScheduled = true;
                    $shouldRemove = false;
                    break;
                case $operation instanceof InstallOperation:
                    $this->io->write('<fg=yellow>Scheduled configure + git:init</fg=yellow>');
                    $this->initScheduled = true;
                    $this->configureScheduled = true;
                    $shouldRemove = false;
                    break;
                case $operation instanceof UninstallOperation:
                    $this->io->write('<fg=yellow>Uninstalling grumphp!</fg=yellow>');
                    $shouldRemove = true;

                    break;
                default:
                    $this->io->write('Unhandled GRUMPHP operation');
            }
        }

        // Remove as quickly as possible before dependencies are removed ....
        if ($shouldRemove) {
            $this->runGrumPhpCommand(self::COMMAND_DEINIT);
        }
    }

    public function runScheduledTasks(Event $event): void
    {
        if ($this->configureScheduled) {
            $this->runGrumPhpCommand(self::COMMAND_CONFIGURE);
        }
        if ($this->initScheduled) {
            $this->runGrumPhpCommand(self::COMMAND_INIT);
        }
    }


    private function fixBrokenComposerPluginUpdate()
    {
        return;

        // TODO ....

        // to avoid issues when Flex is upgraded, we load all PHP classes now
        // that way, we are sure to use all classes from the same version
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ('.php' === substr($file, -4)) {
                class_exists(__NAMESPACE__.str_replace('/', '\\', substr($file, \strlen(__DIR__), -4)));
            }
        }
    }

    /**
     * @param iterable<OperationInterface> $operations
     *
     * @return iterable<OperationInterface>
     */
    private function detectGrumphpOperations(iterable $operations): \Generator
    {
        foreach ($operations as $operation) {
            $package = $this->detectOperationPackage($operation);
            if ($this->guardIsGrumPhpPackage($package)) {
                yield $operation;
            }
        }
    }

    private function detectOperationPackage(OperationInterface $operation): ?PackageInterface
    {
        switch (true) {
            case $operation instanceof UpdateOperation:
                return $operation->getTargetPackage();
            case $operation instanceof InstallOperation:
            case $operation instanceof UninstallOperation:
                return $operation->getPackage();
            default:
                return null;
        }
    }

    private function guardIsGrumPhpPackage(?PackageInterface $package): bool
    {
        if (!$package) {
            return false;
        }

        $normalizedNames = array_map('strtolower', $package->getNames());

        return in_array(self::PACKAGE_NAME, $normalizedNames, true);
    }

    private function guardPluginIsEnabled(): bool
    {
        $extra = $this->composer->getPackage()->getExtra();

        return !(bool) ($extra['grumphp']['disable-plugin'] ?? false);
    }

    private function runGrumPhpCommand(string $command): void
    {
        if (!$grumphp = $this->detectGrumphpExecutable()) {
            $this->io->writeError('GrumPHP can not sniff your commits! (ERR: no-exectuable)');
        }


        $process = proc_open(
            $run = implode(' ', array_map('escapeshellarg', [$grumphp, $command])),
            $descriptors = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
    $pipes = [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ]
        );

        // Check executable which is running:
        if (true || $this->io->isVeryVerbose()) {
            $this->io->write('Running process : '.$run);
        }


        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);


        if ($exitCode !== 0) {
            $this->io->writeError('GrumPHP can not sniff your commits.');
            if (true || $this->io->isVeryVerbose()) {
                $this->io->writeError([$stdout, $stderr]);
            }

            return;
        }

        $this->io->write('<fg=yellow>'.$stdout.'</fg=yellow>');
    }

    private function detectGrumphpExecutable(): ?string
    {
        $config = $this->composer->getConfig();
        $binDir = $config->get('bin-dir');
        $suffixes = ['.phar', '', '.bat'];

        return array_reduce(
            $suffixes,
            function(?string $carry, string $suffix) use ($binDir): ?string {
                $possiblePath = $binDir.DIRECTORY_SEPARATOR.self::APP_NAME.$suffix;
                if ($carry || !file_exists($possiblePath) || !is_executable($possiblePath)) {
                    return $carry;
                }

                return $possiblePath;
            }
        );
    }
}
