<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use Composer\Package\PackageInterface;
use GrumPHP\Util\Filesystem;

class ConfigurationFile
{
    const APP_CONFIG_FILE = 'grumphp.yml';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * ConfigurationFile constructor.
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param PackageInterface|null $package
     */
    public function locate(string $workingDir, PackageInterface $package = null): string
    {
        $defaultPath = $workingDir.DIRECTORY_SEPARATOR.self::APP_CONFIG_FILE;
        $defaultPath = $this->locateConfigFileWithDistSupport($defaultPath);

        if (null !== $package) {
            $defaultPath = $this->useConfigPathFromComposer($package, $defaultPath);
        }

        // Make sure to set the full path when it is declared relative
        // This will fix some issues in windows.
        if (!$this->filesystem->isAbsolutePath($defaultPath)) {
            $defaultPath = $workingDir.DIRECTORY_SEPARATOR.$defaultPath;
        }

        return $defaultPath;
    }

    private function useConfigPathFromComposer(PackageInterface $package, string $defaultPath): string
    {
        $extra = $package->getExtra();
        if (!isset($extra['grumphp']['config-default-path'])) {
            return $defaultPath;
        }

        $composerDefaultPath = $extra['grumphp']['config-default-path'];

        return $this->locateConfigFileWithDistSupport($composerDefaultPath);
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
