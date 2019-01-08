<?php

declare(strict_types=1);

namespace GrumPHP\FileProvider;

use GrumPHP\Collection\FilesCollection;

interface FileProviderInterface
{
    public function getFiles(): FilesCollection;
}
