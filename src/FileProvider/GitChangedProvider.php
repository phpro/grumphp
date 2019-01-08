<?php

declare(strict_types=1);

namespace GrumPHP\FileProvider;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Locator\ChangedFiles;

class GitChangedProvider implements FileProviderInterface
{
    const NAME = "changed";

    /**
     * @var ChangedFiles
     */
    protected $changedFiles;

    public function __construct(ChangedFiles $changedFiles)
    {
        $this->changedFiles = $changedFiles;
    }

    public function getFiles(): FilesCollection
    {
        return $this->changedFiles->locateFromGitRepository();
    }
}
