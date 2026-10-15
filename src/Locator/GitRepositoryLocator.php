<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use Gitonomy\Git\Driver\Cli\CliDriver;
use Gitonomy\Git\Repository;
use GrumPHP\Util\Paths;
use Psr\Log\LoggerInterface;

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

    public function locate(LoggerInterface $logger): Repository
    {
        // gitlib 1 has no drivers: the options of the repository configure git.
        if (!class_exists(CliDriver::class)) {
            return new Repository(
                $this->paths->getGitWorktreeDir(),
                [
                    'working_dir' => $this->paths->getGitWorkingDir(),
                    'logger' => $logger,
                    'inherit_environment_variables' => true,
                ]
            );
        }

        /** @psalm-suppress TooManyArguments, InvalidArgument - Only for gitlib 1, which doesn't run this. */
        return new Repository(
            $this->paths->getGitWorktreeDir(),
            $this->paths->getGitWorkingDir(),
            new CliDriver(logger: $logger)
        );
    }
}
