<?php

declare(strict_types=1);

namespace GrumPHP\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * @psalm-suppress MissingConstructor
 */
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
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var bool
     */
    private $handledPackageEvent = false;

    /**
     * @var bool
     */
    private $configureScheduled = false;

    /**
     * @var bool
     */
    private $initScheduled = false;

    /**
     * @var bool
     */
    private $hasBeenRemoved = false;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * Attach package installation events:.
     *
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::PRE_PACKAGE_INSTALL => 'detectGrumphpAction',
            PackageEvents::POST_PACKAGE_INSTALL => 'detectGrumphpAction',
            PackageEvents::PRE_PACKAGE_UPDATE => 'detectGrumphpAction',
            PackageEvents::PRE_PACKAGE_UNINSTALL => 'detectGrumphpAction',
            ScriptEvents::POST_INSTALL_CMD => 'runScheduledTasks',
            ScriptEvents::POST_UPDATE_CMD => 'runScheduledTasks',
        ];
    }

    /**
     * This method can be called by pre/post package events;
     * We make sure to only run it once. This way Grumphp won't execute multiple times.
     * The goal is to run it as fast as possible.
     * For first install, this should also happen on POST install (because otherwise the plugin doesn't exist yet)
     */
    public function detectGrumphpAction(PackageEvent $event): void
    {
        if ($this->handledPackageEvent || !$this->guardPluginIsEnabled()) {
            $this->handledPackageEvent = true;
            return;
        }

        $this->handledPackageEvent = true;

        $grumPhpOperations = $this->detectGrumphpOperations($event->getOperations());
        if (!count($grumPhpOperations)) {
            return;
        }

        // Check all GrumPHP operations to see if they are unanimously removing GrumPHP
        // For example: an update might trigger an uninstall first - but we don't care about that.
        $removalScheduled = array_reduce(
            $grumPhpOperations,
            function (?bool $theVote, OperationInterface $operation): bool {
                $myVote = $operation instanceof UninstallOperation;

                return null === $theVote ? $myVote : ($theVote && $myVote);
            },
            null
        );

        // Remove immediately once when we are positive about removal. (now that our dependencies are still there)
        if ($removalScheduled) {
            $this->runGrumPhpCommand(self::COMMAND_DEINIT);
            $this->hasBeenRemoved = true;
            return;
        }

        // Schedule install at the end of the process if we don't need to uninstall
        $this->initScheduled = true;
        $this->configureScheduled = true;
    }

    /**
     * Runs the scheduled tasks after an update / install command.
     */
    public function runScheduledTasks(Event $event): void
    {
        if ($this->configureScheduled) {
            $this->runGrumPhpCommand(self::COMMAND_CONFIGURE);
        }

        if ($this->initScheduled) {
            $this->runGrumPhpCommand(self::COMMAND_INIT);
        }
    }

    /**
     * @param OperationInterface[] $operations
     *
     * @return OperationInterface[]
     */
    private function detectGrumphpOperations(array $operations): array
    {
        return array_values(array_filter(
            $operations,
            function (OperationInterface $operation): bool {
                $package = $this->detectOperationPackage($operation);
                return $this->guardIsGrumPhpPackage($package);
            }
        ));
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

    /**
     * This method also detects aliases / replaces statements which makes grumphp-shim possible.
     */
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
            $this->pluginErrored('no-exectuable');
            return;
        }

        // Respect composer CLI settings
        $ansi = $this->io->isDecorated() ? '--ansi' : '--no-ansi';
        $silent = $command === self::COMMAND_CONFIGURE ? '--silent' : '';
        $interaction = $this->io->isInteractive() ? '' : '--no-interaction';

        // Windows requires double double quotes
        // https://bugs.php.net/bug.php?id=49139
        $windowsIsInsane = function (string $command): string {
            return $this->runsOnWindows() ? '"'.$command.'"' : $command;
        };

        // Run command
        $process = @proc_open(
            $run = $windowsIsInsane(implode(' ', array_map(
                function (string $argument): string {
                    return escapeshellarg($argument);
                },
                array_filter([$grumphp, $command, $ansi, $silent, $interaction])
            ))),
            // Map process to current io
            $descriptorspec = array(
                0 => array('file', 'php://stdin', 'r'),
                1 => array('file', 'php://stdout', 'w'),
                2 => array('file', 'php://stderr', 'w'),
            ),
            $pipes = []
        );

        // Check executable which is running:
        if ($this->io->isVerbose()) {
            $this->io->write('Running process : '.$run);
        }

        if (!is_resource($process)) {
            $this->pluginErrored('no-process');
            return;
        }

        // Loop on process until it exits normally.
        do {
            $status = proc_get_status($process);
        } while ($status && $status['running']);

        $exitCode = $status['exitcode'] ?? -1;
        proc_close($process);

        if ($exitCode !== 0) {
            $this->pluginErrored('invalid-exit-code');
            return;
        }
    }

    private function detectGrumphpExecutable(): ?string
    {
        $config = $this->composer->getConfig();
        $binDir = $this->ensurePlatformSpecificDirectorySeparator((string) $config->get('bin-dir'));
        $suffixes = $this->runsOnWindows() ? ['.bat', ''] : ['.phar', ''];

        return array_reduce(
            $suffixes,
            function (?string $carry, string $suffix) use ($binDir): ?string {
                $possiblePath = $binDir.DIRECTORY_SEPARATOR.self::APP_NAME.$suffix;
                if ($carry || !file_exists($possiblePath)) {
                    return $carry;
                }

                return $possiblePath;
            }
        );
    }

    private function runsOnWindows(): bool
    {
        return defined('PHP_WINDOWS_VERSION_BUILD');
    }

    private function ensurePlatformSpecificDirectorySeparator(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function pluginErrored(string $reason): void
    {
        $this->io->writeError('<fg=red>GrumPHP can not sniff your commits! ('.$reason.')</fg=red>');
    }
}
