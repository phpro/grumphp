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

    public function readPath(string $path): string
    {
        return $this->readFromFileInfo(new SplFileInfo($path));
    }

    public function isFile(string $path): bool
    {
        return \is_file($path);
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

    public function guessFile(array $paths, array $fileNames): string
    {
        foreach ($paths as $path) {
            if (!$this->exists($path)) {
                continue;
            }

            if (is_file($path)) {
                return $path;
            }

            foreach ($fileNames as $fileName) {
                $filePath = $this->buildPath($path, $fileName);
                if ($this->exists($filePath)) {
                    return $filePath;
                }
            }
        }

        return $this->buildPath(current($paths), current($fileNames));
    }
}
