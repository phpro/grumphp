<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use Gitonomy\Git\Driver\Cli\CliDriver;
use Gitonomy\Git\Repository;
use GrumPHP\Util\Paths;

class GitRepositoryLocator
{
    /**
     * @var Paths
     */
    private $paths;

    public function __construct(Paths $paths)
    {
        $this->paths = $paths;
    }

    public function locate(CliDriver $driver): Repository
    {
        return new Repository(
            $this->paths->getGitWorktreeDir(),
            $this->paths->getGitWorkingDir(),
            $driver
        );
    }
}
