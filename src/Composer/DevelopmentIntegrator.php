<?php

declare(strict_types=1);

namespace GrumPHP\Composer;

use Composer\Script\Event;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Process\ProcessFactory;
use GrumPHP\Util\Filesystem;

class DevelopmentIntegrator
{
    /**
     * This method makes sure that GrumPHP registers itself during development.
     */
    public static function integrate(Event $event): void
    {
        $filesystem = new Filesystem();

        $composerBinDir = $event->getComposer()->getConfig()->get('bin-dir');
        $executable = dirname(__DIR__, 2).$filesystem->ensureValidSlashes('/bin/grumphp');
        $composerExecutable = $composerBinDir.'/grumphp';
        $filesystem->copy(
            $filesystem->ensureValidSlashes($executable),
            $filesystem->ensureValidSlashes($composerExecutable)
        );

        $commandlineArgs = ProcessArgumentsCollection::forExecutable($composerExecutable);
        $commandlineArgs->add('git:init');

        $process = ProcessFactory::fromArguments($commandlineArgs);
        $process->run();
        if (!$process->isSuccessful()) {
            $event->getIO()->write(
                '<fg=red>GrumPHP can not sniff your commits. Did you specify the correct git-dir?</fg=red>'
            );
            $event->getIO()->write('<fg=red>'.$process->getErrorOutput().'</fg=red>');

            return;
        }

        $event->getIO()->write('<fg=yellow>'.$process->getOutput().'</fg=yellow>');
    }
}
