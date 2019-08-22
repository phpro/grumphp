<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Process\ProcessFactory;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Process\ExecutableFinder;

class GitRepositoryDirLocator
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Resolves the path to the git repository directory (aka as .git).
     * For submodules, it parses the .git file and resolves to the .git/modules/[submodules] directory
     */
    public function locate(string $gitDir): string
    {
        if (!$this->filesystem->isFile($gitDir)) {
            return $gitDir;
        }

        $content = $this->filesystem->readPath($gitDir);
        if (!preg_match('/gitdir:\s+(\S+)/', $content, $matches)) {
            return $gitDir;
        }

        $gitRepositoryDir = $matches[1];

        return $this->filesystem->buildPath(
            dirname($gitDir),
            $gitRepositoryDir
        );
    }
}
