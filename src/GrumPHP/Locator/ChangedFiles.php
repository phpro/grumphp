<?php

namespace GrumPHP\Locator;

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
    public function locate()
    {
        $diff = $this->repository->getWorkingCopy()->getDiffStaged();
        $files = array();
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
