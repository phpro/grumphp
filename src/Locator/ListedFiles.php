<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Util\Paths;
use Symfony\Component\Finder\SplFileInfo;

class ListedFiles
{
    /**
     * @var Paths
     */
    private $paths;

    public function __construct(Paths $paths)
    {
        $this->paths = $paths;
    }

    public function locate(string $fileList): FilesCollection
    {
        $filePaths = preg_split("/\r\n|\n|\r/", $fileList);

        $files = [];
        foreach (array_filter($filePaths) as $file) {
            $relativeFile = $this->paths->makePathRelativeToProjectDir($file);
            $files[] = new SplFileInfo($relativeFile, dirname($relativeFile), $relativeFile);
        }

        return new FilesCollection($files);
    }
}
