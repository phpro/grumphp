<?php

declare(strict_types=1);

namespace GrumPHP\Git;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Exception\ProcessException;
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

    /**
     * The gitonomy run method handles errors differently based on debug (throw) or non-debug (return null) mode.
     * This method makes it possible to run a git command but fallback to a default string if the command fails.
     * It can be used to e.g. fetch git configurations.
     */
    public function tryToRunWithFallback(callable $run, string $fallback): string
    {
        try {
            $result = $run();
        } catch (ProcessException $exception) {
            return $fallback;
        }

        return $result ?? $fallback;
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
