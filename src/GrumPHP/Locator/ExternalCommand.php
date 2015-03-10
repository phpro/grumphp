<?php

namespace GrumPHP\Locator;

use Symfony\Component\Filesystem\Filesystem;

class ExternalCommand implements LocatorInterface
{
    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var string
     */
    protected $binDir;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param string $baseDir
     * @param string $binDir
     * @param Filesystem $filesystem
     */
    public function __construct($baseDir, $binDir, Filesystem $filesystem)
    {
        $this->baseDir = $baseDir;
        $this->binDir = $binDir;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $command
     *
     * @return string
     */
    public function locate($command = '')
    {
        $location = $this->binDir . DIRECTORY_SEPARATOR . $command;

        if (!$this->filesystem->exists($location)) {
            throw new \RuntimeException(sprintf('The executable for "%s" could not be found at: "%s".', $command, $location));
        }

        return $location;
    }
}
