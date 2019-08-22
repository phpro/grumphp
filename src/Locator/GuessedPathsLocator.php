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
     * @var GitWorkingDirLocator
     */
    private $gitWorkingDirLocator;

    /**
     * @var GitRepositoryDirLocator
     */
    private $gitRepositoryDirLocator;

    public function __construct(
        Filesystem $filesystem,
        GitWorkingDirLocator $gitWorkingDirLocator,
        GitRepositoryDirLocator $gitRepositoryDirLocator
    ) {
        $this->filesystem = $filesystem;
        $this->gitWorkingDirLocator = $gitWorkingDirLocator;
        $this->gitRepositoryDirLocator = $gitRepositoryDirLocator;
    }

    public function locate(?string $cliConfigFile): GuessedPaths
    {
        $workingDir = getcwd();
        $cliConfigFile = $this->makeOptionalPathAbsolute($workingDir, $cliConfigFile);
        $cliConfigPath = $cliConfigFile ? dirname($cliConfigFile) : null;
        $projectDirEnv = $this->makeOptionalPathAbsolute($workingDir, (string) ($_SERVER['GRUMPHP_PROJECT_DIR'] ?? ''));

        $gitWorkingDir = $this->filesystem->makePathAbsolute(
            (string) ($_SERVER['GRUMPHP_GIT_WORKING_DIR'] ?? $this->gitWorkingDirLocator->locate()),
            $workingDir
        );
        $gitRepositoryDir = $this->filesystem->makePathAbsolute(
            (string) ($_SERVER['GRUMPHP_GIT_REPOSITORY_DIR'] ?? $this->gitRepositoryDirLocator->locate(
                $this->filesystem->buildPath($gitWorkingDir, '.git')
            )),
            $workingDir
        );

        $composerFilePathname = $this->filesystem->guessFile(
            array_filter([
                $this->makeOptionalPathAbsolute($workingDir, (string) ($_SERVER['GRUMPHP_COMPOSER_DIR'] ?? '')),
                $cliConfigPath,
                $projectDirEnv,
                $workingDir,
                $gitWorkingDir
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

        $binDir = $this->filesystem->guessPath(array_filter([
            $this->makeOptionalPathAbsolute($workingDir, (string) ($_SERVER['GRUMPHP_BIN_DIR'] ?? '')),
            $this->makeOptionalPathAbsolute($composerFilePath, $composerFile->getBinDir())
        ]));

        $composerConfigDefaultPath = $this->makeOptionalPathAbsolute(
            $composerFilePath,
            $composerFile->getConfigDefaultPath()
        );

        $projectDir = $this->filesystem->guessPath([
            $projectDirEnv,
            $this->makeOptionalPathAbsolute($composerFilePath, $composerFile->getProjectPath()),
            $workingDir
        ]);

        $defaultConfigFile = $this->filesystem->guessFile(
            array_filter([
                $cliConfigFile,
                $cliConfigPath,
                $composerConfigDefaultPath,
                $projectDir,
                $workingDir,
                $gitWorkingDir,
            ]),
            [
                'grumphp.yml',
                'grumphp.yaml',
                'grumphp.yml.dist',
                'grumphp.yaml.dist',
            ]
        );

        return new GuessedPaths(
            $gitWorkingDir,
            $gitRepositoryDir,
            $workingDir,
            $projectDir,
            $binDir,
            $composerFile,
            $defaultConfigFile
        );
    }

    private function makeOptionalPathAbsolute(string $baseDir, ?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return $this->filesystem->makePathAbsolute($path, $baseDir);
    }
}
