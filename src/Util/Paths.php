<?php

declare(strict_types=1);

namespace GrumPHP\Util;

use GrumPHP\Configuration\GuessedPaths;

class Paths
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var GuessedPaths
     */
    private $guessedPaths;

    public function __construct(
        Filesystem $filesystem,
        GuessedPaths $guessedPaths
    ) {
        $this->filesystem = $filesystem;
        $this->guessedPaths = $guessedPaths;
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

    public function getProjectDir(): string
    {
        return $this->guessedPaths->getProjectDir();
    }

    public function getGitWorkingDir(): string
    {
        return $this->guessedPaths->getGitWorkingDir();
    }

    public function getGitHooksDir(): string
    {
        return $this->filesystem->buildPath($this->getGitRepositoryDir(), 'hooks');
    }

    public function getGitRepositoryDir(): string
    {
        return $this->guessedPaths->getGitRepositoryDir();
    }

    public function getBinDir(): string
    {
        return $this->guessedPaths->getBinDir();
    }

    public function getConfigFile(): string
    {
        return $this->guessedPaths->getConfigFile();
    }

    public function getProjectDirRelativeToGitDir(): string
    {
        return $this->filesystem->makePathRelative(
            $this->filesystem->realpath($this->getProjectDir()),
            $this->filesystem->realpath($this->getGitWorkingDir())
        );
    }

    public function makePathRelativeToProjectDir(string $filePath): string
    {
        // Transform absolute paths to the git root:
        if ($this->filesystem->isAbsolutePath($filePath)) {
            $filePath = rtrim(
                $this->filesystem->makePathRelative(
                    $this->filesystem->realpath($filePath),
                    $this->getGitWorkingDir()
                ),
                '/\\'
            );
        }

        // Transform from git root to project relative:
        $relativeProjectDir = $this->getProjectDirRelativeToGitDir();
        $relativePath = preg_replace('#^('.preg_quote($relativeProjectDir, '#').')#', '', $filePath);

        if (strpos($relativePath, './') === 0) {
            return substr($relativePath, 2);
        }

        return $relativePath;
    }

    public function makePathRelativeToProjectDirWhenInSubFolder(string $path): string
    {
        if (!$this->filesystem->isPathInFolder($path, $this->getProjectDir())) {
            return $this->filesystem->realpath($path);
        }

        return $this->makePathRelativeToProjectDir($path);
    }
}
