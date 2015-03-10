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
     * @param string $baseDir
     * @param string $binDir
     */
    public function __construct($baseDir, $binDir)
    {
        $this->baseDir = $baseDir;
        $this->binDir = $binDir;
    }

    /**
     * @param string $command
     *
     * @return string
     */
    public function locate($command = '')
    {
        $filesystem = new Filesystem();
        $location = $this->binDir . DIRECTORY_SEPARATOR . $command;

        if (!$filesystem->exists($location)) {
            throw new \RuntimeException(sprintf('The executable for %s could not be found at: %s', $command, $location));
        }

        return $location;
    }
}
