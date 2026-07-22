<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Util\ComposerFile;

class GuessedPaths
{
    /**
     * @var string
     */
    private $gitWorkingDir;

    /**
     * @var string
     */
    private $gitRepositoryDir;

    /**
     * @var string
     */
    private $workingDir;

    /**
     * @var string
     */
    private $projectDir;

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
    private $configFile;

    /**
     * @var string
     */
    private $gitWorktreeDir;

    public function __construct(
        string $gitWorkingDir,
        string $gitRepositoryDir,
        string $workingDir,
        string $projectDir,
        string $binDir,
        ComposerFile $composerFile,
        string $configFile,
        ?string $gitWorktreeDir = null
    ) {
        $this->gitWorkingDir = $gitWorkingDir;
        $this->gitRepositoryDir = $gitRepositoryDir;
        $this->workingDir = $workingDir;
        $this->projectDir = $projectDir;
        $this->binDir = $binDir;
        $this->composerFile = $composerFile;
        $this->configFile = $configFile;
        $this->gitWorktreeDir = $gitWorktreeDir ?? $gitRepositoryDir;
    }

    public function getGitWorkingDir(): string
    {
        return $this->gitWorkingDir;
    }

    public function getGitRepositoryDir(): string
    {
        return $this->gitRepositoryDir;
    }

    public function getGitWorktreeDir(): string
    {
        return $this->gitWorktreeDir;
    }

    public function getWorkingDir(): string
    {
        return $this->workingDir;
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function getBinDir(): string
    {
        return $this->binDir;
    }

    public function getComposerFile(): ComposerFile
    {
        return $this->composerFile;
    }

    public function getConfigFile(): string
    {
        return $this->configFile;
    }
}
