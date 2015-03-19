<?php

namespace GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Filesystem\Filesystem;

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
     * @param $config
     * @param $fileSystem
     */
    public function __construct($config, $fileSystem)
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
        return $this->fileSystem->makePathRelative(realpath($path), $this->getWorkingDir());
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
        $file = $this->getAsciiPath() . $resource . '.txt';
        if (!$this->fileSystem->exists($file)) {
            throw new RuntimeException(sprintf('ASCII file %s could not be found.', $resource));
        }

        return file_get_contents($resource);
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

        return $this->fileSystem->makePathRelative(realpath($gitDir), $this->getWorkingDir());
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

        return $this->fileSystem->makePathRelative(realpath($binDir), $this->getWorkingDir());
    }

    /**
     * Search a command in the bin folder
     *
     * @param $command
     *
     * @return string
     */
    public function getBinCommand($command)
    {
        $location = $this->getBinDir() . $command;
        if (!$this->fileSystem->exists($location)) {
            throw new RuntimeException(sprintf('The BIN command %s could not be found.', $command));
        }

        return $this->getBinDir() . $command;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName()
    {
        return self::HELPER_NAME;
    }
}
