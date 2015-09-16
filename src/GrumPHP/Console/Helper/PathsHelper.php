<?php

namespace GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Exception\FileNotFoundException;
use GrumPHP\Locator\ExternalCommand;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;

/**
 * This class will return all configured paths relative to the working directory.
 *
 * Class PathsHelper
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
     * @param GrumPHP         $config
     * @param Filesystem      $fileSystem
     */
    public function __construct(GrumPHP $config, Filesystem $fileSystem)
    {
        $this->config = $config;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Get the root path of the GrumPHP package:
     *
     * @return string
     */
    public function getGrumPHPPath()
    {
        $path = __DIR__ . '/../../../../';

        return $this->getRelativePath($path);
    }

    /**
     * Get the folder which contains all resources
     *
     * @return string
     */
    public function getResourcesPath()
    {
        return $this->getGrumPHPPath() . 'resources/';
    }

    /**
     * Get the path with all ascii art
     *
     * @return string
     */
    public function getAsciiPath()
    {
        return $this->getResourcesPath() . 'ascii/';
    }

    /**
     * Load an ascii image
     *
     * @param $resource
     *
     * @return string
     */
    public function getAsciiContent($resource)
    {
        $file = $this->config->getAsciiContentPath($resource);

        // Disabled:
        if (is_null($file)) {
            return '';
        }

        // Specified by user:
        if ($this->fileSystem->exists($file)) {
            return file_get_contents($file);
        }

        // Embedded ASCII art:
        $embeddedFile = $this->getAsciiPath() . $file;
        if ($this->fileSystem->exists($embeddedFile)) {
            return file_get_contents($embeddedFile);
        }

        // Error:
        return sprintf('ASCII file %s could not be found.', $file);
    }

    /**
     * This is the directory in which the cli script is initialized.
     * Normally this should be the directory where the composer.json file is located.
     *
     * @return string
     */
    public function getWorkingDir()
    {
        return getcwd();
    }

    /**
     * Find the relative git directory
     *
     * @return string
     */
    public function getGitDir()
    {
        $gitDir = $this->config->getGitDir();
        if (!$this->fileSystem->exists($gitDir)) {
            throw new RuntimeException('The configured GIT directory could not be found.');
        }

        return $this->getRelativePath($gitDir);
    }

    /**
     * Gets the path from where the command needs to be executed in the GIT hook.
     *
     * @return string
     */
    public function getGitHookExecutionPath()
    {
        $gitPath = $this->getGitDir();

        return $this->fileSystem->makePathRelative($this->getWorkingDir(), realpath($gitPath));
    }

    /**
     * Returns the directory where the git hooks are installed.
     *
     * @return string
     */
    public function getGitHooksDir()
    {
        return $this->getGitDir() . '.git/hooks/';
    }

    /**
     * The folder with all git hooks
     *
     * @return string
     */
    public function getGitHookTemplatesDir()
    {
        return $this->getResourcesPath() . 'hooks/';
    }

    /**
     * Find the relative bin directory
     *
     * @return string
     */
    public function getBinDir()
    {
        $binDir = $this->config->getBinDir();
        if (!$this->fileSystem->exists($binDir)) {
            throw new RuntimeException('The configured BIN directory could not be found.');
        }

        return $this->getRelativePath($binDir);
    }

    /**
     * Search a command in the bin folder
     * Note: the command locator is not injected because it needs the relative bin path
     *
     * @param $command
     * @param $forceUnix
     *
     * @return string
     */
    public function getBinCommand($command, $forceUnix = false)
    {
        $commandLocator = new ExternalCommand($this->getBinDir(), new ExecutableFinder());

        return $commandLocator->locate($command, $forceUnix);
    }

    /**
     * @param $path
     *
     * @return string
     * @throws FileNotFoundException If file doesn't exists
     */
    public function getRelativePath($path)
    {
        $path = trim($path);
        $realpath = realpath($path);
        if (false === $realpath) {
            throw new FileNotFoundException($path);
        }

        return $this->fileSystem->makePathRelative($realpath, $this->getWorkingDir());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::HELPER_NAME;
    }
}
