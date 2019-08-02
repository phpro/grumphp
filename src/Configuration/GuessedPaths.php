<?php

declare(strict_types=1);

namespace GrumPHP\Configuration;

use GrumPHP\Util\ComposerFile;

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

    public function __construct(
        string $gitDir,
        string $workingDir,
        string $binDir,
        ComposerFile $composerFile,
        string $defaultConfigFile
    ) {
    
        $this->gitDir = $gitDir;
        $this->workingDir = $workingDir;
        $this->binDir = $binDir;
        $this->composerFile = $composerFile;
        $this->defaultConfigFile = $defaultConfigFile;
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
