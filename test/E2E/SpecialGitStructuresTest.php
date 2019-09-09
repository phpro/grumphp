<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

class SpecialGitStructuresTest extends AbstractE2ETestCase
{
    /** @test */
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

    /** @test */
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
