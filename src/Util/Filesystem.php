<?php

declare(strict_types=1);

namespace GrumPHP\Util;

use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class Filesystem extends SymfonyFilesystem
{
    public function readFromFileInfo(SplFileInfo $file): string
    {
        $handle = $file->openFile('r');
        $content = '';
        while (!$handle->eof()) {
            $content .= $handle->fgets();
        }

        return $content;
    }
}
