<?php

namespace GrumPHP\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
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
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_UPDATE_CMD => 'initializeGitHooks',
            ScriptEvents::POST_INSTALL_CMD => 'initializeGitHooks',
        );
    }

    /**
     * @param Event $event
     */
    public function initializeGitHooks(Event $event)
    {
        $composer = $event->getComposer();
        $binDir = $composer->getConfig()->get('bin-dir');
        $executable = $binDir . '/grumphp';

        $builder = new ProcessBuilder(array('php', $executable, 'git:init'));
        $process = $builder->getProcess();

        $process->run();
        if (!$process->isSuccessful()) {
            $event->getIO()->write('<fg=red>GrumPHP can not sniff your commits. Did you specify the correct git-dir?</fg=red>');
            $event->getIO()->write($process->getErrorOutput());
            return;
        }

        $event->getIO()->write('<fg=yellow>Watch out! GrumPHP is sniffing your commits!<fg=yellow>');
    }
}
