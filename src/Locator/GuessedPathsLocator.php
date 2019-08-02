<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Configuration\GuessedPaths;
use GrumPHP\Util\ComposerFile;
use GrumPHP\Util\Filesystem;

class GuessedPathsLocator
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var GitDirLocator
     */
    private $gitDirLocator;

    public function __construct(Filesystem $filesystem, GitDirLocator $gitDirLocator)
    {
        $this->filesystem = $filesystem;
        $this->gitDirLocator = $gitDirLocator;
    }

    public function locate(?string $cliConfigFile): GuessedPaths
    {
        $cliConfigPath = $cliConfigFile ? dirname($cliConfigFile) : null;
        $workingDir = getcwd();
        $gitDir = $this->gitDirLocator->locate();
        $composerFilePathname = $this->filesystem->guessFile(
            array_filter([
                $cliConfigPath,
                $workingDir,
                $gitDir
            ]),
            [
                'composer.json'
            ]
        );
        $composerFilePath = dirname($composerFilePathname);
        $composerFile = new ComposerFile(
            $composerFilePathname,
            $this->filesystem->exists($composerFilePathname)
                ? json_decode($this->filesystem->readFromFileInfo(new \SplFileInfo($composerFilePathname)), true)
                : []
        );

        $binDir = $composerFile->getBinDir();
        $binDir = $this->filesystem->isAbsolutePath($binDir)
            ? $binDir
            : $this->filesystem->buildPath($composerFilePath, $binDir);

        $defaultConfigFile = $this->filesystem->guessFile(
            array_filter([
                $cliConfigFile,
                $cliConfigPath,
                $composerFile->getConfigDefaultPath(),
                $workingDir,
                $gitDir,
            ]),
            [
                'grumphp.yml',
                'grumphp.yaml',
                'grumphp.yml.dist',
                'grumphp.yaml.dist',
            ]
        );

        return new GuessedPaths($gitDir, $workingDir, $binDir, $composerFile, $defaultConfigFile);
    }
}
