<?php declare(strict_types=1);

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

class GitPreCommitContext implements ContextInterface
{
    /**
     * @var FilesCollection
     */
    private $files;

    /**
     * @param FilesCollection $files
     */
    public function __construct(FilesCollection $files)
    {
        $this->files = $files;
    }

    /**
     * @return FilesCollection
     */
    public function getFiles()
    {
        return $this->files;
    }
}
