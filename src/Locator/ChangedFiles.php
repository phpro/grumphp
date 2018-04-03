<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Git.
 */
class ChangedFiles
{
    /**
     * @var Repository
     */
    protected $repository;
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Repository $repository, Filesystem $filesystem)
    {
        $this->repository = $repository;
        $this->filesystem = $filesystem;
    }

    public function locateFromGitRepository(): FilesCollection
    {
        $diff = $this->repository->getWorkingCopy()->getDiffStaged();

        return $this->parseFilesFromDiff($diff);
    }

    public function locateFromRawDiffInput(string $rawDiff): FilesCollection
    {
        $diff = Diff::parse($rawDiff);
        $diff->setRepository($this->repository);

        return $this->parseFilesFromDiff($diff);
    }

    private function parseFilesFromDiff(Diff $diff): FilesCollection
    {
        $files = [];
        /** @var File $file */
        foreach ($diff->getFiles() as $file) {
            $fileName = $file->isRename() ? $file->getNewName() : $file->getName();
            $fileObject = new SplFileInfo($fileName, dirname($fileName), $fileName);

            if ($file->isDeletion() || !$this->filesystem->exists($fileObject->getPathname())) {
                continue;
            }

            $files[] = $fileObject;
        }

        return new FilesCollection($files);
    }
}
