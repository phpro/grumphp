<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

use PHPUnit\Framework\Attributes\Test;

class SpecialGitStructuresTest extends AbstractE2ETestCase
{
    #[Test]
    function it_runs_inside_a_submodule()
    {
        $subModule = $this->mkdir('submodule');
        $main = $this->mkdir('main');

        $this->initializeGit($subModule);
        $this->appendToGitignore($subModule);
        $this->initializeComposer($subModule);
        $grumphpFile = $this->initializeGrumphpConfig($subModule);
        $this->installComposer($subModule);
        $this->ensureHooksExist($subModule);
        $this->enableValidatePathsTask($grumphpFile, $subModule);
        $this->commitAll($subModule);

        $this->initializeGit($main);
        $submoduleInMain = $this->initializeGitSubModule($main, $subModule);
        $this->installComposer($submoduleInMain);
        $this->runGrumphp($submoduleInMain);
    }

    #[Test]
    function it_runs_inside_a_worktree()
    {
        $main = $this->mkdir('main');

        $this->initializeGit($main);
        $this->appendToGitignore($main);
        $this->initializeComposer($main);
        $grumphpFile = $this->initializeGrumphpConfig($main);
        $this->installComposer($main);
        $this->ensureHooksExist($main);

        // A file that lives on the main branch but not on the worktree's branch:
        $this->dumpFile($this->filesystem->buildPath($main, 'only-on-main.txt'), 'main');
        $this->enableValidatePathsTask($grumphpFile, $main);
        $this->commitAll($main);

        // A linked worktree on a diverging branch that drops the main-only file:
        $worktree = $this->addGitWorktree($main, 'worktree', 'feature');
        $this->filesystem->remove($this->filesystem->buildPath($worktree, 'only-on-main.txt'));
        $this->commitAllWithoutHook($worktree);

        $this->installComposer($worktree);
        $worktreeGrumphpFile = $this->initializeGrumphpConfig($worktree);
        $this->enableValidatePathsTask($worktreeGrumphpFile, $worktree);

        // GrumPHP must list the worktree's branch, so only-on-main.txt stays out of the file list:
        $this->runGrumphp($worktree);
    }

    #[Test]
    function it_handles_partial_commits()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->filesystem->dumpFile(
            $this->filesystem->buildPath($this->rootDir, 'new.txt'),
            'This file should not be in partial commit!'
        );
        $this->commitModifiedAndDeleted();
    }
}
