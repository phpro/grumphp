<?php declare(strict_types=1);

namespace GrumPHP\Util;

use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class Filesystem extends SymfonyFilesystem
{
    /**
     * @return string|null
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
