<?php

namespace GrumPHP\Locator;

use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use SplFileInfo;

/**
 * Class RegisteredFiles
 *
 * @package GrumPHP\Locator
 */
class RegisteredFiles implements LocatorInterface
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
        $filePaths = explode(PHP_EOL, $allFiles);

        $files = array();
        foreach ($filePaths as $file) {
            $files[] = new SplFileInfo($file);

        }

        return new FilesCollection($files);
    }
}
