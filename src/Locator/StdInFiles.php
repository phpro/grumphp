<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Collection\FilesCollection;

class StdInFiles
{
    /**
     * @var ChangedFiles
     */
    private $changedFilesLocator;

    /**
     * @var ListedFiles
     */
    private $listedFiles;

    public function __construct(
        ChangedFiles $changedFilesLocator,
        ListedFiles $listedFiles
    ) {
        $this->changedFilesLocator = $changedFilesLocator;
        $this->listedFiles = $listedFiles;
    }

    public function locate(string $stdIn): FilesCollection
    {
        if (preg_match('/^diff --git/', $stdIn)) {
            return $this->changedFilesLocator->locateFromRawDiffInput($stdIn);
        }

        return $this->listedFiles->locate($stdIn);
    }
}
