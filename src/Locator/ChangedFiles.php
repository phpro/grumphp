<?php

namespace GrumPHP\Locator;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\Context\GitPrePushContext;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Git
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

    /**
     * @param Repository $repository
     * @param Filesystem $filesystem
     */
    public function __construct(Repository $repository, Filesystem $filesystem)
    {
        $this->repository = $repository;
        $this->filesystem = $filesystem;
    }

    /**
     * @return FilesCollection
     */
    public function locateFromGitRepository()
    {
        $diff = $this->repository->getWorkingCopy()->getDiffStaged();
//      if($context instanceof GitPrePushContext ) {
        $actualbranch = trim(shell_exec('git name-rev --name-only HEAD'));
        $diff = explode("\n",trim(shell_exec('git diff --name-only origin/' . $actualbranch . '..HEAD --oneline')));
        foreach ($diff as $file) {
            $fileObject = new SplFileInfo($file, dirname($file), $file);
            $files[] = $fileObject;
        }

        $diff = new FilesCollection($files);
//      }
        if($diff instanceof Gitonomy\Git\Diff\Diff) {
            return $this->parseFilesFromDiff($diff);
        }
        return $diff;
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
