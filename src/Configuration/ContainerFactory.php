<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Locator\GitRepositoryDirLocator;
use GrumPHP\Locator\GitWorkingDirLocator;
use GrumPHP\Locator\GuessedPathsLocator;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Process\ExecutableFinder;

class ContainerFactory
{
    public static function build(InputInterface $input, OutputInterface $output): Container
    {
        $cliConfigFile = $input->getParameterOption(['--config', '-c'], null);
        $guessedPaths = self::guessPaths($cliConfigFile);

        // Build the service container:
        $container = ContainerBuilder::buildFromConfiguration($guessedPaths->getConfigFile());
        $container->set('console.input', $input);
        $container->set('console.output', $output);
        $container->set(GuessedPaths::class, $guessedPaths);

        return $container;
    }

    private static function guessPaths(?string $cliConfigFile): GuessedPaths
    {
        $fileSystem = new Filesystem();

        return (new GuessedPathsLocator(
            $fileSystem,
            new GitWorkingDirLocator(new ExecutableFinder()),
            new GitRepositoryDirLocator($fileSystem)
        ))->locate($cliConfigFile);
    }
}
