<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use SplFileInfo;

class AsciiLocator
{
    /**
     * @var array|null
     */
    private $asciiConfig;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(?array $asciiConfig, Filesystem $filesystem, Paths $paths)
    {
        $this->asciiConfig = $asciiConfig;
        $this->filesystem = $filesystem;
        $this->paths = $paths;
    }

    public function locate(string $resource): string
    {
        $file = $this->grabAsciiResourceFromConfig($resource);

        // Disabled:
        if (null === $file) {
            return '';
        }

        // Specified by user:
        if ($this->filesystem->exists($file)) {
            return $this->filesystem->readFromFileInfo(new SplFileInfo($file));
        }

        // Embedded ASCII art:
        $embeddedFile = $this->filesystem->buildPath($this->paths->getInternalAsciiPath(), $file);
        if ($this->filesystem->exists($embeddedFile)) {
            return $this->filesystem->readFromFileInfo(new SplFileInfo($embeddedFile));
        }

        // Error:
        return sprintf('ASCII file %s could not be found.', $file);
    }

    private function grabAsciiResourceFromConfig(string $resource): ?string
    {
        if (null === $this->asciiConfig) {
            return null;
        }

        $paths = $this->asciiConfig;
        if (!array_key_exists($resource, $paths)) {
            return null;
        }

        // Deal with multiple ascii files by returning one at random.
        if (\is_array($paths[$resource])) {
            shuffle($paths[$resource]);
            return reset($paths[$resource]);
        }

        return $paths[$resource];
    }
}
