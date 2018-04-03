<?php

declare(strict_types=1);

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

class GitPreCommitContext implements ContextInterface
{
    private $files;

    public function __construct(FilesCollection $files)
    {
        $this->files = $files;
    }

    public function getFiles(): FilesCollection
    {
        return $this->files;
    }
}
