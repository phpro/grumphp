<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Configuration\GuessedPaths;
use GrumPHP\Util\Filesystem;

class EnrichedGuessedPathsFromDotEnvLocator
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function locate(GuessedPaths $guessedPaths): GuessedPaths
    {
        $workingDir = $guessedPaths->getWorkingDir();
        $projectDir = $this->filesystem->makePathAbsolute(
            (string) ($_SERVER['GRUMPHP_PROJECT_DIR'] ?? $guessedPaths->getProjectDir()),
            $workingDir
        );
        $gitWorkingDir = $this->filesystem->makePathAbsolute(
            (string) ($_SERVER['GRUMPHP_GIT_WORKING_DIR'] ?? $guessedPaths->getGitWorkingDir()),
            $workingDir
        );
        $gitRepositoryDir = $this->filesystem->makePathAbsolute(
            (string) ($_SERVER['GRUMPHP_GIT_REPOSITORY_DIR'] ?? $guessedPaths->getGitRepositoryDir()),
            $workingDir
        );
        $binDir = $this->filesystem->makePathAbsolute(
            (string) ($_SERVER['GRUMPHP_BIN_DIR'] ?? $guessedPaths->getBinDir()),
            $workingDir
        );

        return new GuessedPaths(
            $gitWorkingDir,
            $gitRepositoryDir,
            $workingDir,
            $projectDir,
            $binDir,
            $guessedPaths->getComposerFile(),
            $guessedPaths->getConfigFile()
        );
    }
}
