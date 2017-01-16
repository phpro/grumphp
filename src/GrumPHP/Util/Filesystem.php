<?php

namespace GrumPHP\Util;

use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class Filesystem extends SymfonyFilesystem
{
    /**
     * @param SplFileInfo $file
     * @return string
     */
    public function readFromFileInfo(SplFileInfo $file)
    {
        $handle = $file->openFile('r');
        $content = '';
        while (!$handle->eof()) {
            $content .= $handle->fgets();
        }

        return $content;
    }
}
