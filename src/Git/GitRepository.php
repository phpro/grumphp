<?php

declare(strict_types=1);

namespace GrumPHP\Git;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Repository;
use Gitonomy\Git\WorkingCopy;
use GrumPHP\Locator\GitRepositoryLocator;

/**
 *
 * Small wrapper on top of the gitonomy repository class.
 * This makes it possible to lazy load the repository and add additional features on top of that library.
 */
class GitRepository
{
    /**
     * @var GitRepositoryLocator
     */
    private $repositoryLocator;

    /**
     * @var array
     */
    private $repositoryOptions;

    /**
     * @var ?Repository
     */
    private $repository;

    public function __construct(GitRepositoryLocator $repositoryLocator, array $repositoryOptions)
    {
        $this->repositoryLocator = $repositoryLocator;
        $this->repositoryOptions = $repositoryOptions;
    }

    public function getWorkingCopy(): WorkingCopy
    {
        return $this->getRepository()->getWorkingCopy();
    }

    public function run(string $command, array $args): ?string
    {
        return $this->getRepository()->run($command, $args);
    }

    public function createRawDiff(string $rawDiff): Diff
    {
        $diff = Diff::parse($rawDiff);
        $diff->setRepository($this->getRepository());

        return $diff;
    }

    /**
     * The Gitonomy GIT repository throws an exception when git does not exist during construction.
     * We don't always want this message to display (For example: during list or help commands)
     * Therefor: the internal repository is lazy loaded
     */
    private function getRepository(): Repository
    {
        if (!$this->repository) {
            $this->repository = $this->repositoryLocator->locate($this->repositoryOptions);
        }

        return $this->repository;
    }
}
