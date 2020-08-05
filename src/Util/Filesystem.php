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
        $realPath = realpath($path);
        if ($realPath === false) {
            throw new FileNotFoundException(sprintf('Path "%s" does not exist.', $path));
        }

        return $realPath;
    }

    public function makePathAbsolute(string $path, string $basePath): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return $this->buildPath($basePath, $path);
    }

    public function isPathInFolder(string $path, string $folder): bool
    {
        $realPath = $this->realpath($path);
        $realDirectory = $this->isFile($realPath) ? dirname($realPath) : $realPath;

        return strpos($realDirectory, $this->realpath($folder)) === 0;
    }

    public function buildPath(string $baseDir, string $path): string
    {
        return $baseDir.DIRECTORY_SEPARATOR.$path;
    }

    public function guessPath(array $paths): string
    {
        foreach ($paths as $path) {
            if ($this->exists($path) && is_dir($path)) {
                return $path;
            }
        }

        return current($paths);
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

        $firstPath = current($paths);
        $firstName = current($fileNames);

        if (preg_match('#'.preg_quote($firstName, '#').'$#', $firstPath)) {
            return $firstPath;
        }

        return $this->buildPath($firstPath, $firstName);
    }

    public function ensureUnixPath(string $path): string
    {
        // Unix systems know best ...
        if (DIRECTORY_SEPARATOR === '/') {
            return $path;
        }

        // Convert backslashes, remove duplicate slashes and transform drive letter to uppercase:
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('|(?<=.)/+|', '/', $path);
        if (':' === ($path[1] ?? '')) {
            $path = ucfirst($path);
        }

        return $path;
    }

    public function ensureValidSlashes(string $path): string
    {
        // Unix systems know best ...
        if (DIRECTORY_SEPARATOR === '/') {
            return $path;
        }

        // Convert / slash to \ on windows:
        $path = str_replace('/', '\\', $path);
        $path = preg_replace('|(?<=.)\\\\+|', '\\', $path);
        if (':' === ($path[1] ?? '')) {
            $path = ucfirst($path);
        }

        return $path;
    }

    /**
     * This method can be used to create a backup of current file.
     * It used md5 to hash the content.
     * So multiple backups of a file can exist (depending on the content)
     */
    public function backupFile(string $fileName, ?string $hashOfNewContent = null): void
    {
        if (!$this->isFile($fileName)) {
            return;
        }

        if (!$hash = md5_file($fileName)) {
            return;
        }

        // Skip backup if the new file has the same content as the old one.
        if ($hashOfNewContent && $hashOfNewContent === $hash) {
            return;
        }

        $this->copy($fileName, sprintf('%s.%s.backup', $fileName, $hash));
    }
}
