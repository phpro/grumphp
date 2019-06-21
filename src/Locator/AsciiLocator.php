<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use SplFileInfo;

class AsciiLocator
{
    /**
     * @var GrumPHP
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(GrumPHP $config, Filesystem $filesystem, Paths $paths)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->paths = $paths;
    }

    public function locate(string $resource): string
    {
        $file = $this->config->getAsciiContentPath($resource);

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
}
