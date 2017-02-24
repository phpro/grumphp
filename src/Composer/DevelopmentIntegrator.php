<?php

namespace GrumPHP\Composer;

use Composer\Script\Event;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Process\ProcessBuilder;

class DevelopmentIntegrator
{
    /**
     * This method makes sure that GrumPHP registers itself during development.
     */
    public static function integrate(Event $event)
    {
        $filesystem = new Filesystem();

        $composerBinDir = $event->getComposer()->getConfig()->get('bin-dir');
        $executable = getcwd() . '/bin/grumphp';
        $composerExecutable =  $composerBinDir . '/grumphp';
        $filesystem->copy(
            self::noramlizePath($executable),
            self::noramlizePath($composerExecutable)
        );

        $process = ProcessBuilder::create([$composerExecutable, 'git:init'])->getProcess();
        $process->run();
        if (!$process->isSuccessful()) {
            $event->getIO()->write(
                '<fg=red>GrumPHP can not sniff your commits. Did you specify the correct git-dir?</fg=red>'
            );
            $event->getIO()->write('<fg=red>' . $process->getErrorOutput() . '</fg=red>');
            return;
        }

        $event->getIO()->write('<fg=yellow>' . $process->getOutput() . '</fg=yellow>');
    }

    /**
     * @param $path
     *
     * @return string
     */
    private static function noramlizePath($path)
    {
        return strtr($path, '/', DIRECTORY_SEPARATOR);
    }
}
