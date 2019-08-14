<?php

declare(strict_types=1);

namespace GrumPHP\Util;

use GrumPHP\Configuration\GrumPHP;

class Paths
{
    /**
     * @var GrumPHP
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        Filesystem $filesystem,
        GrumPHP $config
    ) {
        $this->config = $config;
        $this->filesystem = $filesystem;
    }

    /**
     * The root dir of this package!
     */
    public function getGrumPHPExecutableRootDir(): string
    {
        return dirname(__DIR__, 2);
    }

    public function getInternalResourcesDir(): string
    {
        return $this->filesystem->buildPath($this->getGrumPHPExecutableRootDir(), 'resources');
    }

    public function getInternalAsciiPath(): string
    {
        return $this->filesystem->buildPath($this->getInternalResourcesDir(), 'ascii');
    }

    public function getInternalGitHookTemplatesPath(): string
    {
        return $this->filesystem->buildPath($this->getInternalResourcesDir(), 'hooks');
    }

    public function getGrumPHPConfigDir(): string
    {
        return \dirname($this->config->getConfigFile());
    }

    public function getComposerConfigDir(): string
    {
        return \dirname($this->config->getComposerFile()->getPath());
    }

    public function getWorkingDir(): string
    {
        return $this->config->getWorkingDir();
    }

    public function getProjectDir(): string
    {
        return $this->config->getProjectDir();
    }

    public function getGitDir(): string
    {
        return $this->config->getGitDir();
    }

    public function getBinDir(): string
    {
        return $this->config->getBinDir();
    }

    public function getProjectDirRelativeToGitDir(): string
    {
        return $this->filesystem->makePathRelative(
            $this->getProjectDir(),
            $this->getGitDir()
        );
    }

    public function makePathRelativeToProjectDir(string $filePath): string
    {
        $relativeProjectDir = $this->getProjectDirRelativeToGitDir();
        $relativePath = preg_replace('#^('.preg_quote($relativeProjectDir, '#').')#', '', $filePath);

        if (strpos($relativePath, './') === 0) {
            return substr($relativePath, 2);
        }

        return $relativePath;
    }
}
