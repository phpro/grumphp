<?php

namespace GrumPHP\Locator;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ConfigurationFile
 *
 * @package GrumPHP\Locator
 */
class ConfigurationFile
{
    const APP_CONFIG_FILE = 'grumphp.yml';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * ConfigurationFile constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $workingDir
     *
     * @return string
     */
    public function locate($workingDir)
    {
        $defaultPath = $workingDir . DIRECTORY_SEPARATOR .  self::APP_CONFIG_FILE;
        $defaultPath = $this->locateConfigFileWithDistSupport($defaultPath);
        $defaultPath = $this->useConfigPathFromComposer($workingDir, $defaultPath);

        // Make sure to set the full path when it is declared relative
        // This will fix some issues in windows.
        if (!$this->filesystem->isAbsolutePath($defaultPath)) {
            $defaultPath = $workingDir . DIRECTORY_SEPARATOR . $defaultPath;
        }

        return $defaultPath;
    }

    /**
     * @param $workingDir
     * @param $defaultPath
     *
     * @return string
     */
    private function useConfigPathFromComposer($workingDir, $defaultPath)
    {
        $composerFile = $workingDir . DIRECTORY_SEPARATOR . 'composer.json';
        if (!$this->filesystem->exists($composerFile)) {
            return $defaultPath;
        }

        $composerData = json_decode(file_get_contents($composerFile), true);
        if (!isset($composerData['extra']['grumphp']['config-default-path'])) {
            return $defaultPath;
        }

        $composerDefaultPath = $composerData['extra']['grumphp']['config-default-path'];
        return $this->locateConfigFileWithDistSupport($composerDefaultPath);
    }

    /**
     * @param $defaultPath
     *
     * @return string
     */
    private function locateConfigFileWithDistSupport($defaultPath)
    {
        $distPath = (strpos($defaultPath, -5) !== '.dist') ? $defaultPath . '.dist' : $defaultPath;
        if ($this->filesystem->exists($defaultPath) || !$this->filesystem->exists($distPath)) {
            return $defaultPath;
        }

        return $distPath;
    }
}
