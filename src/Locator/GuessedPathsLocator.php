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
        $projectDirEnv = (string) ($_SERVER['GRUMPHP_PROJECT_DIR'] ?? '');

        $gitDir = (string) ($_SERVER['GRUMPHP_GIT_DIR'] ?? $this->gitDirLocator->locate());
        $composerFilePathname = $this->filesystem->guessFile(
            array_filter([
                (string) ($_SERVER['GRUMPHP_COMPOSER_DIR'] ?? ''),
                $cliConfigPath,
                $projectDirEnv,
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

        $binDir = $this->makeComposerPathAbsolute(
            $composerFilePath,
            (string) ($_SERVER['GRUMPHP_BIN_DIR'] ?? $composerFile->getBinDir())
        );

        $composerConfigDefaultPath = $this->makeComposerPathAbsolute(
            $composerFilePath,
            $composerFile->getConfigDefaultPath()
        );

        $projectDir = $this->filesystem->guessDir([
            $projectDirEnv,
            $this->makeComposerPathAbsolute($composerFilePath, $composerFile->getProjectPath()),
            $workingDir
        ]);

        $defaultConfigFile = $this->filesystem->guessFile(
            array_filter([
                $cliConfigFile,
                $cliConfigPath,
                $composerConfigDefaultPath,
                $projectDir,
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

        return new GuessedPaths($gitDir, $workingDir, $projectDir, $binDir, $composerFile, $defaultConfigFile);
    }

    private function makeComposerPathAbsolute(string $composerFilePath, ?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return $this->filesystem->isAbsolutePath($path)
            ? $path
            : $this->filesystem->buildPath($composerFilePath, $path);
    }
}
