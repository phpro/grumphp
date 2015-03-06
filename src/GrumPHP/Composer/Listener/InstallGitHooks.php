<?php


namespace GrumPHP\Composer\Listener;

use Composer\Script\Event;

/**
 * Class InstallGitHooks
 *
 * @package GrumPHP\Composer\Listener
 */
class InstallGitHooks
{

    /**
     * @param Event $event
     */
    public static function initializeGitHooks(Event $event)
    {
        $config = $event->getComposer()->getConfig();
    }

}
