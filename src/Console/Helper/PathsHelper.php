<?php declare(strict_types=1);

namespace GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\FileNotFoundException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Locator\ExternalCommand;
use GrumPHP\Util\Filesystem;
use SplFileInfo;
use Symfony\Component\Console\Helper\Helper;

/**
 * This class will return all configured paths relative to the working directory.
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
     * @var ExternalCommand
     */
    private $externalCommandLocator;

    /**
     * @var string
     */
    private $defaultConfigPath;

    public function __construct(
        GrumPHP $config,
        Filesystem $fileSystem,
        ExternalCommand $externalCommandLocator,
        string $defaultConfigPath
    ) {
        $this->config = $config;
        $this->fileSystem = $fileSystem;
        $this->externalCommandLocator = $externalCommandLocator;
        $this->defaultConfigPath = $defaultConfigPath;
    }

    /**
     * Get the root path of the GrumPHP package:
     */
    public function getGrumPHPPath(): string
    {
        $path = __DIR__ . '/../../..';

        return $this->getRelativePath($path);
    }

    /**
     * Get the folder which contains all resources
     */
    public function getResourcesPath(): string
    {
        return $this->getGrumPHPPath() . 'resources/';
    }

    /**
     * Get the path with all ascii art
     */
    public function getAsciiPath(): string
    {
        return $this->getResourcesPath() . 'ascii/';
    }

    /**
     * Load an ascii image
     *
     *
     * @return string|null
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
            return $this->fileSystem->readFromFileInfo(new SplFileInfo($file));
        }

        // Embedded ASCII art:
        $embeddedFile = $this->getAsciiPath() . $file;
        if ($this->fileSystem->exists($embeddedFile)) {
            return $this->fileSystem->readFromFileInfo(new SplFileInfo($embeddedFile));
        }

        // Error:
        return sprintf('ASCII file %s could not be found.', $file);
    }

    /**
     * This is the directory in which the cli script is initialized.
     * Normally this should be the directory where the composer.json file is located.
     */
    public function getWorkingDir(): string
    {
        return getcwd();
    }

    /**
     * Find the relative git directory
     */
    public function getGitDir(): string
    {
        $gitDir = $this->config->getGitDir();
        if (!$this->fileSystem->exists($gitDir)) {
            throw new RuntimeException('The configured GIT directory could not be found.');
        }

        return $this->getRelativePath($gitDir);
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
        return $this->getGitDir() . '.git/hooks/';
    }

    /**
     * The folder with all git hooks
     */
    public function getGitHookTemplatesDir(): string
    {
        return $this->getResourcesPath() . 'hooks/';
    }

    /**
     * Find the relative bin directory
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
     * Search a command in the bin folder
     * Note: the command locator is not injected because it needs the relative bin path
     */
    public function getBinCommand($command, $forceUnix = false): string
    {
        return $this->externalCommandLocator->locate($command, $forceUnix);
    }

    /**
     * @throws FileNotFoundException If file doesn't exists
     */
    public function getRelativePath($path): string
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

    /**
     * @return mixed
     */
    public function getAbsolutePath($path)
    {
        $path = trim($path);
        $realpath = realpath($path);
        if (false === $realpath) {
            throw new FileNotFoundException($path);
        }

        return $realpath;
    }

    public function getPathWithTrailingSlash(string $path): string
    {
        if (!$path) {
            return $path;
        }

        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public function getDefaultConfigPath(): string
    {
        return $this->defaultConfigPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::HELPER_NAME;
    }
}
