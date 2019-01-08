<?php

declare(strict_types=1);

namespace GrumPHP\FileProvider;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Locator\RegisteredFiles;

class DefaultProvider implements FileProviderInterface
{
    const NAME = "default";

    /**
     * @var RegisteredFiles
     */
    protected $registeredFiles;

    public function __construct(RegisteredFiles $registeredFiles)
    {
        $this->registeredFiles = $registeredFiles;
    }

    public function getFiles(): FilesCollection
    {
        return $this->registeredFiles->locate();
    }
}
