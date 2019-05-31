<?php

declare(strict_types=1);

namespace GrumPHP\Util;

use SplFileInfo;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
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

    public function realpath(string $path): string
    {
        if (!$this->exists($path)) {
            throw new FileNotFoundException(sprintf('Path "%s" does not exist.', $path));
        }

        return realpath($path);
    }

    public function buildPath(string $baseDir, string $path): string
    {
        return $baseDir.DIRECTORY_SEPARATOR.$path;
    }

    public function guessDir(array $dirs, callable $validator): string
    {
        foreach ($dirs as $dir) {
            if ($validator($dir)) {
                return $dir;
            }
        }

        return current($dirs) ?? '';
    }
}
