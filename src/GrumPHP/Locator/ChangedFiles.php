<?php

namespace GrumPHP\Locator;

use Gitonomy\Git\Diff\File;
use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use SplFileInfo;

/**
 * Class Git
 *
 * @package GrumPHP\Locator
 */
class ChangedFiles implements LocatorInterface
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
        $diff = $this->repository->getDiff('HEAD');
        $files = array();
        /** @var File $file */
        foreach ($diff->getFiles() as $file) {
            if ($file->isDeletion()) {
                continue;
            }

            $fileName = $file->isRename() ? $file->getNewName() : $file->getName();
            $files[] = new SplFileInfo($fileName);
        }

        return new FilesCollection($files);
    }
}
