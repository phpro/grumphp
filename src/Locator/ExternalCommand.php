<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use Symfony\Component\Process\ExecutableFinder;

class ExternalCommand
{
    /**
     * @var string
     */
    protected $binDir;

    /**
     * @var ExecutableFinder
     */
    protected $executableFinder;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(string $binDir, ExecutableFinder $executableFinder, Filesystem $filesystem)
    {
        $this->binDir = rtrim($binDir, '/\\');
        $this->executableFinder = $executableFinder;
        $this->filesystem = $filesystem;
    }

    public static function loadWithPaths(Paths $paths, ExecutableFinder $executableFinder, Filesystem $filesystem): self
    {
        return new self(
            $paths->getBinDir(),
            $executableFinder,
            $filesystem
        );
    }

    public function locate(string $command, bool $forceUnix = false): string
    {
        // Search executable:
        $executable = $this->executableFinder->find($command, null, [$this->binDir]);
        if (!$executable) {
            throw new RuntimeException(
                sprintf('The executable for "%s" could not be found.', $command)
            );
        }

        if ($forceUnix) {
            $executable = $this->filesystem->ensureUnixPath($executable);
        }

        return $executable;
    }
}
