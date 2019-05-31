<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Util\ComposerFile;
use Symfony\Component\Filesystem\Filesystem;

class ConfigurationFile
{
    const APP_CONFIG_FILE = 'grumphp.yml';

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function locate(string $workingDir, ComposerFile $composerFile): string
    {
        $defaultPath = $workingDir.DIRECTORY_SEPARATOR.self::APP_CONFIG_FILE;
        $defaultPath = $this->locateConfigFileWithDistSupport($defaultPath);
        $defaultPath = $this->useConfigPathFromComposer($composerFile, $defaultPath);

        // Make sure to set the full path when it is declared relative
        // This will fix some issues in windows.
        if (!$this->filesystem->isAbsolutePath($defaultPath)) {
            $defaultPath = $workingDir.DIRECTORY_SEPARATOR.$defaultPath;
        }

        return $defaultPath;
    }

    private function useConfigPathFromComposer(ComposerFile $composerFile, string $defaultPath): string
    {
        $composerDefaultPath = $composerFile->getConfigDefaultPath();

        return $this->locateConfigFileWithDistSupport($composerDefaultPath ?: $defaultPath);
    }

    private function locateConfigFileWithDistSupport(string $defaultPath): string
    {
        $distPath = (substr($defaultPath, -5) !== '.dist') ? $defaultPath . '.dist' : $defaultPath;
        if ($this->filesystem->exists($defaultPath) || !$this->filesystem->exists($distPath)) {
            return $defaultPath;
        }

        return $distPath;
    }
}
