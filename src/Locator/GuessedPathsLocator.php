<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Configuration\GuessedPaths;
use GrumPHP\Exception\RuntimeException;
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
            (string) ($_SERVER['GRUMPHP_GIT_WORKING_DIR'] ?? $this->safelyLocateGitWorkingDir($workingDir)),
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
            $this->makeOptionalPathAbsolute(
                $composerFilePath,
                $this->ensureOptionalArgumentWithValidSlashes($composerFile->getBinDir())
            )
        ]));

        $composerConfigDefaultPath = $this->makeOptionalPathAbsolute(
            $composerFilePath,
            $this->ensureOptionalArgumentWithValidSlashes($composerFile->getConfigDefaultPath())
        );

        $projectDir = $this->filesystem->guessPath([
            $projectDirEnv,
            $this->makeOptionalPathAbsolute(
                $composerFilePath,
                $this->ensureOptionalArgumentWithValidSlashes($composerFile->getProjectPath())
            ),
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

    private function ensureOptionalArgumentWithValidSlashes(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return $this->filesystem->ensureValidSlashes($path);
    }

    /**
     * The git locator fails when no git dir can be found.
     * However : that might degrade the user experience when just running the info commands on the cli tool.
     * Gitonomy will detect invalid git dirs anyways. So it is ok to fall back to e.g. the current working dir.
     */
    private function safelyLocateGitWorkingDir(string $fallbackDir): string
    {
        try {
            return $this->gitWorkingDirLocator->locate();
        } catch (RuntimeException $e) {
            return $fallbackDir;
        }
    }
}
