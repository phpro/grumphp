<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

class FolderStructuresTest extends AbstractE2ETestCase
{
    /** @test */
    function it_has_all_config_files_in_root_git_dir()
    {
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }

    /** @test */
    function it_has_project_subfolder()
    {
        $projectDir = $this->mkdir('project');
        $this->initializeComposer($projectDir);
        $grumphpFile = $this->initializeGrumphpConfig($projectDir);
        $this->installComposer($projectDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $projectDir);
        $this->commitAll();
        $this->runGrumphp($projectDir);
    }

    /** @test */
    function it_can_define_conventions_at_another_location()
    {
        $composerFile = $this->initializeComposer($this->rootDir);
        $conventionsDir = $this->mkdir('vendor/phpro/conventions');
        $grumphpFile = $this->initializeGrumphpConfig($conventionsDir);
        $this->registerGrumphpDefaultPathInComposer($composerFile, $grumphpFile);
        $this->mergeGrumphpConfig($grumphpFile, ['parameters' => ['git_dir' => '.']]);
        $this->installComposer($this->rootDir);

        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }

    /** @test */
    function it_has_grumphp_in_root_but_composer_in_project_folder()
    {
        $this->markTestSkipped('This flow is broken: git hooks are placed in project dir instead of git dir.');

        $projectDir = $this->mkdir('project');
        $composerFile = $this->initializeComposer($projectDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir, $projectDir);
        $this->registerGrumphpDefaultPathInComposer($composerFile, $grumphpFile);
        $this->installComposer($projectDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $projectDir);

        $this->commitAll();
        $this->runGrumphp($projectDir);
    }

    /** @test */
    function it_has_composer_in_root_but_grumphp_in_project_folder()
    {
        $projectDir = $this->mkdir('project');
        $composerFile = $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($projectDir, $this->rootDir);
        $this->registerGrumphpDefaultPathInComposer($composerFile, $grumphpFile);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $projectDir);

        $this->commitAll();
        $this->runGrumphp($projectDir);
    }

    /**
     * TODO
     *
     * - test git submodule  -> #459
     * - test git commit -a and -p
     */
}
