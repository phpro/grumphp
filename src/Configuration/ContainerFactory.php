<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Configuration\Environment\DotEnvRegistrar;
use GrumPHP\Configuration\Environment\PathsRegistrar;
use GrumPHP\Configuration\Model\EnvConfig;
use GrumPHP\Locator\EnrichedGuessedPathsFromDotEnvLocator;
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

        // Load environment config:
        $config = $container->get(EnvConfig::class);
        assert($config instanceof EnvConfig);

        // Setup the environment and overwrite guessed paths if needed:
        DotEnvRegistrar::register($config);
        $guessedPaths = self::enrichGuessedPathsWithDotEnv($container, $guessedPaths);

        //  Make sure that important paths are loaded first:
        PathsRegistrar::prepend($guessedPaths->getBinDir(), ...$config->getPaths());

        // Set instances:
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

    private static function enrichGuessedPathsWithDotEnv(Container $container, GuessedPaths $guessedPaths): GuessedPaths
    {
        $locator = $container->get(EnrichedGuessedPathsFromDotEnvLocator::class);
        assert($locator instanceof EnrichedGuessedPathsFromDotEnvLocator);

        return $locator->locate($guessedPaths);
    }
}
