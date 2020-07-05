<?php

declare(strict_types=1);

namespace GrumPHP\Composer;

use Composer\Script\Event;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Process\ProcessFactory;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

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
        $process = self::fixInternalComposerProcessVersion($commandlineArgs);

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

    /**
     * Composer contains symfony/process:v2.8 internally
     * This causes this integration hook to fail. (Since the Process class is being loaded from internal composer PHAR)
     * @see https://github.com/composer/composer/blob/1.9.1/composer.lock#L772-L773
     *
     * This one can be removed once composer udpates its dependencies to at least symfony/process 3.3
     */
    private static function fixInternalComposerProcessVersion(
        ProcessArgumentsCollection $commandlineArgs
    ): Process {
        if (class_exists(ProcessBuilder::class, true)) {
            return ProcessBuilder::create($commandlineArgs->getValues())->getProcess();
        }

        return ProcessFactory::fromArguments($commandlineArgs);
    }
}
