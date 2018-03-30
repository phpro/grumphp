<?php

namespace GrumPHP\Locator;

use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use Symfony\Component\Finder\SplFileInfo;

class RegisteredFiles
{
    /**
     * @var Repository
     */
    private $repository;

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
        $allFiles = trim($this->repository->run('ls-files'));
        $filePaths = preg_split("/\r\n|\n|\r/", $allFiles);

        $files = [];
        foreach ($filePaths as $file) {
            $fileInfo = new SplFileInfo($file, \dirname($file), $file);
            if (!$fileInfo->isLink()) {
                $files[] = $fileInfo;
            }
        }

        return new FilesCollection($files);
    }
}
