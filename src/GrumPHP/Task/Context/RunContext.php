<?php

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

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
