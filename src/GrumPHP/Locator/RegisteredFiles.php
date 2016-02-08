<?php

namespace GrumPHP\Locator;

use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use Symfony\Component\Finder\SplFileInfo;
use Psr\Log\LoggerInterface;

/**
 * Class RegisteredFiles
 *
 * @package GrumPHP\Locator
 */
class RegisteredFiles extends AbstractFiles
{
    /**
     * @return FilesCollection
     */
    public function locate()
    {
        $allFiles = trim($this->repository->run('ls-files'));
        $filePaths = preg_split("/\r\n|\n|\r/", $allFiles);

        $files = array();
        foreach ($filePaths as $file) {
            $files[] = new SplFileInfo($file, dirname($file), $file);
        }

        return new FilesCollection($files);
    }
}
