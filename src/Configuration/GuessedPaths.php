<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Locator\GitDirLocator;
use GrumPHP\Util\ComposerFile;
use GrumPHP\Util\Filesystem;

class GuessedPaths
{
    /**
     * @var string
     */
    private $gitDir;

    /**
     * @var string
     */
    private $workingDir;

    /**
     * @var ComposerFile
     */
    private $composerFile;

    /**
     * @var string
     */
    private $binDir;

    /**
     * @var string
     */
    private $defaultConfigFile;

    private function __construct()
    {
    }

    public static function guess(Filesystem $filesystem, GitDirLocator $gitDirLocator): self
    {
        $guessed = new self();
        $guessed->workingDir = getcwd();
        $guessed->gitDir = $gitDirLocator->locate();

        $composerFilePath = $filesystem->guessFile(
            [
                $guessed->workingDir,
                $guessed->gitDir
            ],
            [
                'composer.json'
            ]
        );
        $guessed->composerFile = new ComposerFile(
            $composerFilePath,
            $filesystem->exists($composerFilePath)
                    ? json_decode($filesystem->readFromFileInfo(new \SplFileInfo($composerFilePath)), true)
                : []
        );

        $guessed->binDir = $guessed->composerFile->getBinDir();
        $guessed->defaultConfigFile = $filesystem->guessFile(
            array_filter([
                $guessed->composerFile->getConfigDefaultPath(),
                $guessed->workingDir,
                $guessed->gitDir,
            ]),
            [
                'grumphp.yml',
                'grumphp.yaml',
                'grumphp.yml.dist',
                'grumphp.yaml.dist',
            ]
        );

        return $guessed;
    }

    public function getGitDir(): string
    {
        return $this->gitDir;
    }

    public function getWorkingDir(): string
    {
        return $this->workingDir;
    }

    public function getBinDir(): string
    {
        return $this->binDir;
    }

    public function getComposerFile(): ComposerFile
    {
        return $this->composerFile;
    }

    public function getDefaultConfigFile(): string
    {
        return $this->defaultConfigFile;
    }
}
