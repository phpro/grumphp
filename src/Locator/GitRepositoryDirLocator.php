<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use GrumPHP\Util\Filesystem;

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
     * For submodules, it parses the .git file and resolves to the .git/modules/[submodules] directory.
     * For worktrees, it resolves to the common repository root, since worktrees share the main
     * repository's hooks. Use locateWorktreeGitDir() when the worktree's own git dir is required.
     */
    public function locate(string $gitDir): string
    {
        return $this->resolveGitDir($gitDir, true);
    }

    /**
     * Resolves the git directory used to read files and diffs.
     * For worktrees this returns the worktree's own git dir (.git/worktrees/[id]) instead of collapsing
     * to the common repository root, so file listing and diffs reflect the worktree's branch.
     * For submodules and normal checkouts this is identical to locate().
     */
    public function locateWorktreeGitDir(string $gitDir): string
    {
        return $this->resolveGitDir($gitDir, false);
    }

    private function resolveGitDir(string $gitDir, bool $collapseWorktreeToRoot): string
    {
        if (!$this->filesystem->isFile($gitDir)) {
            return $gitDir;
        }

        $content = $this->filesystem->readPath($gitDir);
        if (!preg_match('/gitdir:\s+(\S+)/', $content, $matches)) {
            return $gitDir;
        }

        $gitRepositoryDir = $matches[1];

        if ($this->isWorktree($gitRepositoryDir)) {
            return $collapseWorktreeToRoot
                ? $this->locateWorktreeRoot($gitRepositoryDir)
                : $gitRepositoryDir;
        }

        return $this->filesystem->buildPath(
            dirname($gitDir),
            $gitRepositoryDir
        );
    }

    /**
     * If a given path (from gitdir value) is absolute and there is a commondir file, it is
     * a worktree.
     */
    private function isWorktree(string $gitDir): bool
    {
        return $this->filesystem->isAbsolutePath($gitDir)
            && $this->filesystem->isFile($gitDir.DIRECTORY_SEPARATOR.'commondir');
    }

    /**
     * Retreiving repository dir for worktree nominally returns path to the configured worktree,
     * which does not hold hooks. We need to resolve the actual repository root.
     *
     * Example directory structure:
     * ```
     * /project
     *   .git/
     *     .git/hooks/
     *     .git/worktrees/
     *       worktree1
     *         commondir: relative path to /project/.git
     * /worktree1
     *   .git: file with path to /project/.git/worktrees/worktree1
     * ```
     */
    private function locateWorktreeRoot(string $gitRepositoryDir): string
    {
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
}
