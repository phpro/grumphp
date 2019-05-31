<?php

declare(strict_types=1);

namespace GrumPHP\Util;

use GrumPHP\Configuration\GrumPHP;
use SplFileInfo;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class Filesystem extends SymfonyFilesystem
{
    /**
     * @var GrumPHP
     */
    private $config;

    public function __construct(GrumPHP $config)
    {
        $this->config = $config;
    }

    public function readFromFileInfo(SplFileInfo $file): string
    {
        $handle = $file->openFile('r');
        $content = '';
        while (!$handle->eof()) {
            $content .= $handle->fgets();
        }

        return $content;
    }

    public function getProjectDir(): string
    {
        return dirname($this->config->getConfigFile());
    }

    public function getGitDir(): string
    {
        $gitDir = $this->config->getGitDir();
        if ($this->isAbsolutePath($gitDir)) {
            return $gitDir;
        }

        $relativeGitDir = $this->getProjectDir() . DIRECTORY_SEPARATOR . $this->ltrimSlashes($gitDir);

        return $this->realpath($relativeGitDir);
    }

    public function getRelativeProjectDir(): string
    {
        return $this->makePathRelative(
            $this->getProjectDir(),
            $this->getGitDir()
        );
    }

    public function getRelativeGitDir(): string
    {
        return $this->makePathRelative(
            $this->getGitDir(),
            $this->getProjectDir()
        );
    }

    public function makePathRelativeToProjectDir(string $filePath): string
    {
        $relativeProjectDir = $this->getRelativeProjectDir();
        $relativePath = preg_replace('#^('.preg_quote($relativeProjectDir, '#').')#', '', $filePath);

        if (strpos($relativePath, './') === 0) {
            return substr($relativePath, 2);
        }

        return $relativePath;
    }

    public function realpath(string $path): string
    {
        if (!$this->exists($path)) {
            throw new FileNotFoundException(sprintf('Path "%s" does not exist.', $path));
        }

        return realpath($path);
    }

    private function ltrimSlashes(string $path): string
    {
        return ltrim($path, '\\/');
    }
}
