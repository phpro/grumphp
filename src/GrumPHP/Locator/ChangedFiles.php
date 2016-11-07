<?php

namespace GrumPHP\Locator;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Git
 *
 * @package GrumPHP\Locator
 */
class ChangedFiles
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return FilesCollection
     */
    public function locateFromGitRepository()
    {
        $diff = $this->repository->getWorkingCopy()->getDiffStaged();

        return $this->parseFilesFromDiff($diff);
    }

    /**
     * @param string $rawDiff
     *
     * @return FilesCollection
     */
    public function locateFromRawDiffInput($rawDiff)
    {
        $diff = Diff::parse($rawDiff);
        $diff->setRepository($this->repository);

        return $this->parseFilesFromDiff($diff);
    }

    /**
     * @param Diff $diff
     *
     * @return FilesCollection
     */
    private function parseFilesFromDiff(Diff $diff)
    {
        $files = [];
        /** @var File $file */
        foreach ($diff->getFiles() as $file) {
            if ($file->isDeletion()) {
                continue;
            }

            $fileName = $file->isRename() ? $file->getNewName() : $file->getName();
            $files[] = new SplFileInfo($fileName, dirname($fileName), $fileName);
        }

        return new FilesCollection($files);
    }
}
