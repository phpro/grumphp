<?php

namespace GrumPHP\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\Installer\InstallerEvent;
use Composer\IO\IOInterface;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
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
     * @var GrumPHP
     */
    private $config;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;

        $config = $composer->getConfig();
        $grumphp = $config->get('grumphp');
        $this->config = new GrumPHP($grumphp);

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
            'post-install' => 'initializeGitHooks',
            'post-update' => 'initializeGitHooks',
            'pre-dependencies-solving' => 'appendQualityCheckerOperations',
        );
    }

    public function initializeGitHooks(Event $event)
    {
        $config = $event->getComposer()->getConfig();
    }

    public function appendQualityCheckerOperations(InstallerEvent $installerEvent)
    {
//        $installerEvent->getRequest()->install('squizlabs/php_codesniffer', new VersionConstraint('=', '2.3.0.0'));
    }
}
