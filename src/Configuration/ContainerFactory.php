<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Locator\GitDirLocator;
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

        // Make sure to register bin dir in PATHS
        $guessedPaths->getComposerFile()->ensureProjectBinDirInSystemPath();

        // Build the service container:
        $container = ContainerBuilder::buildFromConfiguration($guessedPaths->getDefaultConfigFile());
        $container->set('console.input', $input);
        $container->set('console.output', $output);
        $container->set(GuessedPaths::class, $guessedPaths);

        return $container;
    }

    private static function guessPaths(?string $cliConfigFile): GuessedPaths
    {
        return (new GuessedPathsLocator(
            new Filesystem(),
            new GitDirLocator(new ExecutableFinder())
        ))->locate($cliConfigFile);
    }
}
