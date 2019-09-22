<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Exception\RuntimeException;
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

    public function __construct(string $binDir, ExecutableFinder $executableFinder)
    {
        $this->binDir = rtrim($binDir, '/\\');
        $this->executableFinder = $executableFinder;
    }

    public static function loadWithPaths(Paths $paths, ExecutableFinder $executableFinder): self
    {
        return new self(
            $paths->getBinDir(),
            $executableFinder
        );
    }

    public function locate(string $command): string
    {
        // Search executable:
        $executable = $this->executableFinder->find($command, null, [$this->binDir]);
        if (!$executable) {
            throw new RuntimeException(
                sprintf('The executable for "%s" could not be found.', $command)
            );
        }

        return $executable;
    }
}
