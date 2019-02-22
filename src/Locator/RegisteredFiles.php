<?php

declare(strict_types=1);

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

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function locate(): FilesCollection
    {
        $allFiles = trim($this->repository->run('ls-files'));
        $filePaths = preg_split("/\r\n|\n|\r/", $allFiles);

        $files = [];
        foreach ($filePaths as $file) {
            $files[] = new SplFileInfo($file, dirname($file), $file);
        }

        return new FilesCollection($files);
    }
}
