<?php declare(strict_types=1);

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

class RunContext implements ContextInterface
{
    /**
     * @var FilesCollection
     */
    private $files;

    public function __construct(FilesCollection $files)
    {
        $this->files = $files;
    }

    /**
     * @return FilesCollection
     */
    public function getFiles(): FilesCollection
    {
        return $this->files;
    }
}
