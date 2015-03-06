<?php

namespace GrumPHP\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\InstallerEvent;
use Composer\IO\IOInterface;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Plugin\PluginInterface;
use GrumPHP\Composer\Listener\InstallGitHooks;
use GrumPHP\Configuration\GrumPHP;

class QualityCheckerPlugin implements PluginInterface, EventSubscriberInterface
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

        $config = $composer->getConfig();

        $binDir = $config->get('bin-dir');
        $grumphp = $config->get('grumphp');

        $config = new GrumPHP($grumphp);

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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'post-install' => array(new InstallGitHooks(), 'initializeGitHooks'),
            'post-update' => array(new InstallGitHooks(), 'initializeGitHooks'),
            'pre-dependencies-solving' => 'appendQualityCheckerOperations',
        );
    }

    public function appendQualityCheckerOperations(InstallerEvent $installerEvent)
    {
//        $installerEvent->getRequest()->install('squizlabs/php_codesniffer', new VersionConstraint('=', '2.3.0.0'));
    }
}
