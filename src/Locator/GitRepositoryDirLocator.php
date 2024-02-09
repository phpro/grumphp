<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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

        if ($this->filesystem->isAbsolutePath($gitRepositoryDir)) {
            if (!$this->filesystem->isFile($gitRepositoryDir.DIRECTORY_SEPARATOR.'commondir')) {
                throw new RuntimeException('The git directory for worktree could not be found.');
            }

            $worktreeRelativeRoot = trim(
                $this->filesystem->readPath(
                    $gitRepositoryDir.DIRECTORY_SEPARATOR.'commondir'
                )
            );

            return $this->filesystem->realpath(
                $this->filesystem->makePathAbsolute(
                    $worktreeRelativeRoot,
                    $gitRepositoryDir
                )
            );
        }

        return $this->filesystem->buildPath(
            dirname($gitDir),
            $gitRepositoryDir
        );
    }
}
