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

    /**
     * @see https://gist.github.com/swichers/027d5ae903350cbd4af8
     */
    private function runGrumPhpCommand(string $command): void
    {
        if (!$grumphp = $this->detectGrumphpExecutable()) {
            $this->pluginErrored('no-exectuable');
            return;
        }

        $process = @proc_open(
            $run = implode(' ', array_map('escapeshellarg', [$grumphp, $command])),
            $descriptorspec = array(
                // Must use php://stdin(out) in order to allow display of command output
                // and the user to interact with the process.
                0 => array('file', 'php://stdin', 'r'),
                1 => array('file', 'php://stdout', 'w'),
                2 => array('pipe', 'w'),
            ),
    $pipes = [],
            null,
            array_merge($this->getDefaultEnv(), ['SHELL_INTERACTIVE' => 1])
        );

        // TODO : Check why proccess is marked as inactive ...

        // Check executable which is running:
        if (true || $this->io->isVeryVerbose()) {
            $this->io->write('Running process : '.$run);
        }

        if (!is_resource($process)) {
            $this->pluginErrored('no-process');
            return;
        }

        // Loop on process until it exits normally.
        $stderr = [];
        do {
            $status = proc_get_status($process);
            // If our stderr pipe has data, grab it for use later.
            if (isset($pipes[2]) && !feof($pipes[2])) {
                // Stack errors as they come in...
                $stderr[] =  fgets($pipes[2]);
            }
        } while ($status['running']);


        // According to documentation, the exit code is only valid the first call
        // after a process is finished. We can't rely on the return value of
        // proc_close because proc_get_status will read the exit code first.
        $exitCode = $status['exitcode'];
        proc_close($process);

        if ($exitCode !== 0) {
            $this->pluginErrored('invalid-exit-code', $stderr);
            return;
        }

        // STDOut will be written since the pipe points to php://stdout ... ;-)
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

    private function pluginErrored(string $reason, array $stdErr = [])
    {
        $this->io->writeError('<fg=red>GrumPHP can not sniff your commits! ('.$reason.')</fg=red>');

        if (count($stdErr) && (true || $this->io->isVerbose())) {
            $this->io->write($stdErr);
        }
    }

    /**
     * @see symfony/process
     */
    private function getDefaultEnv(): array
    {
        $env = [];

        foreach ($_SERVER as $k => $v) {
            if (\is_string($v) && false !== $v = getenv($k)) {
                $env[$k] = $v;
            }
        }

        foreach ($_ENV as $k => $v) {
            if (\is_string($v)) {
                $env[$k] = $v;
            }
        }

        return $env;
    }
}
