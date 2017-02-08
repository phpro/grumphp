<?php

namespace GrumPHP\Locator;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
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
        return $this->parseFilesFromDiff($diff);
    }
    
    /**
     * @return FilesCollection
     */
    public function locateFromGitPushedRepository()
    {
        $local_branch = explode("\n", $this->repository->run('name-rev', array('--name-only', 'HEAD')));
        $tracking_branch = explode("\n", str_replace('refs/heads/', '', $this->repository->run('config', array('branch.'.$local_branch[0].'.merge'))));
        $tracking_remote = explode("\n", $this->repository->run('config', array('branch.'.$local_branch[0].'.remote')));
        $diff = explode("\n", $this->repository->run('diff', array($tracking_branch[0].'/'.$tracking_remote[0].'..HEAD', '--name-only', '--oneline')));
        foreach ($diff as $file) {
            $fileObject = new SplFileInfo($file, dirname($file), $file);
            $files[] = $fileObject;
        }

        $diff = new FilesCollection($files);
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
