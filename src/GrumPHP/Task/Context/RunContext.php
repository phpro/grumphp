<?php

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Console\Helper\PathsHelper;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class RunContext
 *
 * @package GrumPHP\Task\Context
 */
class RunContext implements ContextInterface
{
    /**
     * @var FilesCollection
     */
    private $files;

    /**
     * @var PathsHelper
     */
    private $paths;

    /**
     * @param FilesCollection $files
     */
    public function __construct(FilesCollection $files, PathsHelper $paths)
    {
        $this->files = $files;
        $this->paths = $paths;
    }

    /**
     * @param $relativePath boolean
     *
     * @return FilesCollection
     */
    public function getFiles($relativePath = true)
    {
        if ($relativePath) {
            return $this->files;
        }

        $self  = $this;
        $files = $this->files->map(function ($item) use ($self) {
            $pathName =  $this->paths->getGitDir() . $item->getPathname();
            return new SplFileInfo($pathName, $item->getRelativePath(), $item->getRelativePathname());

        });

        return $files;
    }
}
