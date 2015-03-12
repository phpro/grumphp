<?php

namespace GrumPHP\Locator;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class ExternalCommand implements LocatorInterface
{
    /**
     * @var string
     */
    protected $binDir;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param string $binDir
     * @param Filesystem $filesystem
     */
    public function __construct($binDir, Filesystem $filesystem)
    {
        $this->binDir = $binDir;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $command
     *
     * @return string
     *
     * @throws RuntimeException if the command can not be found
     */
    public function locate($command = '')
    {
        $location = $this->binDir . DIRECTORY_SEPARATOR . $command;

        if (!$this->filesystem->exists($location)) {
            throw new RuntimeException(sprintf('The executable for "%s" could not be found at: "%s".', $command, $location));
        }

        return $location;
    }
}
