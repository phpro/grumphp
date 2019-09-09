<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Git\GitRepository;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Git.
 */
class ChangedFiles
{
    /**
     * @var GitRepository
     */
    private $repository;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(GitRepository $repository, Filesystem $filesystem, Paths $paths)
    {
        $this->repository = $repository;
        $this->filesystem = $filesystem;
        $this->paths = $paths;
    }

    public function locateFromGitRepository(): FilesCollection
    {
        $diff = $this->repository->getWorkingCopy()->getDiffStaged();

        return $this->parseFilesFromDiff($diff);
    }

    public function locateFromRawDiffInput(string $rawDiff): FilesCollection
    {
        $diff = $this->repository->createRawDiff($rawDiff);

        return $this->parseFilesFromDiff($diff);
    }

    private function parseFilesFromDiff(Diff $diff): FilesCollection
    {
        $files = [];
        /** @var File $file */
        foreach ($diff->getFiles() as $file) {
            $fileObject = $this->makeFileRelativeToProjectDir($file);
            if ($file->isDeletion() || !$this->filesystem->exists($fileObject->getPathname())) {
                continue;
            }

            $files[] = $fileObject;
        }

        return new FilesCollection($files);
    }

    private function makeFileRelativeToProjectDir(File $file): SplFileInfo
    {
        $filePath = $this->paths->makePathRelativeToProjectDir(
            $file->isRename() ? $file->getNewName() : $file->getName()
        );

        return new SplFileInfo($filePath, dirname($filePath), $filePath);
    }
}
