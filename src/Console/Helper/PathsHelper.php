<?php

declare(strict_types=1);

namespace GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Configuration\GuessedPaths;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Exception\FileNotFoundException;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use SplFileInfo;
use Symfony\Component\Console\Helper\Helper;

/**
 * This class will return all configured paths relative to the working directory.
 * @deprecated Try to use Paths or Filesystem instead
 * @see Filesystem
 * @see Paths
 */
class PathsHelper extends Helper
{
    const HELPER_NAME = 'paths';

    /**
     * @var GrumPHP
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var GuessedPaths
     */
    private $guessedPaths;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(
        GrumPHP $config,
        Filesystem $fileSystem,
        GuessedPaths $guessedPaths,
        Paths $paths
    ) {
        $this->config = $config;
        $this->fileSystem = $fileSystem;
        $this->guessedPaths = $guessedPaths;
        $this->paths = $paths;
    }

    /**
     * This is the directory in which the cli script is initialized.
     * Normally this should be the directory where the composer.json file is located.
     *
     * @deprecated use Paths::getWorkingDir() instead
     * @see Paths::getWorkingDir()
     */
    public function getWorkingDir(): string
    {
        return $this->paths->getWorkingDir();
    }

    /**
     * Find the relative git directory.
     *
     * @deprecated use Paths::getGitDirRelativeToConfig() instead
     * @see Paths::getGitDirRelativeToConfig()
     */
    public function getGitDir(): string
    {
        return $this->paths->getGitDirRelativeToConfig();
    }

    /**
     * Gets the path from where the command needs to be executed in the GIT hook.
     */
    public function getGitHookExecutionPath(): string
    {
        $gitPath = $this->getGitDir();

        return $this->fileSystem->makePathRelative($this->getWorkingDir(), $this->getAbsolutePath($gitPath));
    }

    /**
     * Returns the directory where the git hooks are installed.
     */
    public function getGitHooksDir(): string
    {
        $gitPath = $this->getGitDir();
        $absoluteGitPath = $this->getAbsolutePath($gitPath);
        $gitRepoPath = $absoluteGitPath.'/.git';

        if (is_file($gitRepoPath)) {
            $fileContent = $this->fileSystem->readFromFileInfo(new SplFileInfo($gitRepoPath));
            if (preg_match('/gitdir:\s+(\S+)/', $fileContent, $matches)) {
                $relativePath = $this->getRelativePath($matches[1]);
                return $this->getRelativePath($gitPath.$relativePath.'/hooks/');
            }
        }

        return $gitPath.'.git/hooks/';
    }

    /**
     * The folder with all git hooks.
     */
    public function getGitHookTemplatesDir(): string
    {
        return $this->paths->getInternalGitHookTemplatesPath().'/';
    }

    /**
     * Find the relative bin directory.
     */
    public function getBinDir(): string
    {
        $binDir = $this->config->getBinDir();
        if (!$this->fileSystem->exists($binDir)) {
            throw new RuntimeException('The configured BIN directory could not be found.');
        }

        return $this->getRelativePath($binDir);
    }

    /**
     * @throws FileNotFoundException If file doesn't exists
     */
    public function getRelativePath(string $path): string
    {
        $realpath = $this->getAbsolutePath($path);

        return $this->fileSystem->makePathRelative($realpath, $this->getWorkingDir());
    }

    /**
     * This method will return a relative path to a file of directory if it lives in the current project.
     * When the file is not located in the current project, the absolute path to the file is returned.
     *
     *
     * @throws FileNotFoundException
     */
    public function getRelativeProjectPath(string $path): string
    {
        $realPath = $this->getAbsolutePath($path);
        $gitPath = $this->getAbsolutePath($this->getGitDir());

        if (0 !== strpos($realPath, $gitPath)) {
            return $realPath;
        }

        return rtrim($this->getRelativePath($realPath), '\\/');
    }

    public function getAbsolutePath(string $path): string
    {
        $path = trim($path);
        $realpath = realpath($path);
        if (false === $realpath) {
            throw new FileNotFoundException($path);
        }

        return $realpath;
    }

    public function getPathWithTrailingSlash(string $path = null): ?string
    {
        if (!$path) {
            return $path;
        }

        return rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    /**
     * @deprecated
     */
    public function getDefaultConfigPath(): string
    {
        return $this->guessedPaths->getDefaultConfigFile();
    }

    public function getName(): string
    {
        return self::HELPER_NAME;
    }
}
