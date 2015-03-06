<?php

namespace GrumPHP\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\InstallerEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Process\ProcessBuilder;

class GrumPHPPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        /*
         * TODO: check if tools are available
         *
         * foreach (array_keys($grumphp) as $tool) {
            if (file_exists($binDir . '/' . $tool)) {
                $this->io->write('Would create hook for ' . $tool);
            } else {
                $this->io->writeError($tool . ' binary not found');
            }
        }*/
    }

    /**
     * @param Composer $composer
     *
     * @return GrumPHP
     */
    protected function getConfig(Composer $composer)
    {
        $config = $composer->getConfig();
        $grumphp = $config->get('grumphp');
        return new GrumPHP($grumphp);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            Installer\PackageEvents::POST_PACKAGE_INSTALL => 'initializeGitHooks',
            Installer\PackageEvents::POST_PACKAGE_UPDATE => 'initializeGitHooks',
            Installer\PackageEvents::POST_PACKAGE_UNINSTALL => 'removeGitHooks',
            Installer\InstallerEvents::PRE_DEPENDENCIES_SOLVING => 'appendQualityCheckerOperations',
        );
    }

    /**
     * @param Installer\PackageEvent $event
     */
    public function initializeGitHooks(Installer\PackageEvent $event)
    {
        $repo = $event->getInstalledRepo();

        var_dump($event->getName());
        var_dump($event->getArguments());
        var_dump($event->getFlags());

        return;


        $composer = $event->getComposer();
        $config = $this->getConfig($composer);
        $binDir = $composer->getConfig()->get('bin-dir');
        $executable = $binDir . '/grumphp';

        $builder = new ProcessBuilder(array('php', $executable, 'git:init'));
        $builder->add('--base-dir=' . $config->getBaseDir());
        $process = $builder->getProcess();

        $event->getIO()->write($process->getCommandLine());
        $process->run();

        if (!$process->isSuccessful()) {
            $event->getIO()->write('GrumPHP can not sniff your commits. Did you specify the correct git-dir?');
            $event->getIO()->write($process->getOutput());
            return;
        }

        $event->getIO()->write('Watch out! GrumPHP is sniffing your commits!');
    }

    public function appendQualityCheckerOperations(InstallerEvent $installerEvent)
    {
//        $installerEvent->getRequest()->install('squizlabs/php_codesniffer', new VersionConstraint('=', '2.3.0.0'));
    }
}
