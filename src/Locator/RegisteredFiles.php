<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Git\GitRepository;
use GrumPHP\Util\Paths;

class RegisteredFiles
{
    /**
     * @var GitRepository
     */
    private $repository;

    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var ListedFiles
     */
    private $listedFiles;

    public function __construct(GitRepository $repository, Paths $paths, ListedFiles $listedFiles)
    {
        $this->repository = $repository;
        $this->paths = $paths;
        $this->listedFiles = $listedFiles;
    }

    public function locate(): FilesCollection
    {
        // Make sure to only return the files that are registered to GIT inside current project directory:
        $allFiles = trim((string) $this->repository->run('ls-files', [$this->paths->getProjectDir()]));

        return $this->listedFiles->locate($allFiles);
    }
}
