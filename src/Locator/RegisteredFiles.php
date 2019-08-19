<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Util\Paths;
use Symfony\Component\Finder\SplFileInfo;

class RegisteredFiles
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(Repository $repository, Paths $paths)
    {
        $this->repository = $repository;
        $this->paths = $paths;
    }

    public function locate(): FilesCollection
    {
        // Make sure to only return the files that are registered to GIT inside current project directory:
        $allFiles = trim($this->repository->run('ls-files', [$this->paths->getProjectDir()]));
        $filePaths = preg_split("/\r\n|\n|\r/", $allFiles);

        $files = [];
        foreach (array_filter($filePaths) as $file) {
            $relativeFile = $this->paths->makePathRelativeToProjectDir($file);
            $files[] = new SplFileInfo($relativeFile, dirname($relativeFile), $relativeFile);
        }

        return new FilesCollection($files);
    }
}
