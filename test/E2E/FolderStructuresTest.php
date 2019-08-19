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
        $this->installComposer($this->rootDir);

        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }

    /** @test */
    function it_has_grumphp_in_root_but_composer_in_project_folder()
    {
        $projectDir = $this->mkdir('project');
        $composerFile = $this->initializeComposer($projectDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);
        $this->registerGrumphpDefaultPathInComposer($composerFile, $grumphpFile);
        $this->registerGrumphpProjectPathInComposer($composerFile, $projectDir);
        $this->installComposer($projectDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $projectDir);

        $this->commitAll();
        $this->runGrumphp($projectDir);

        // Since composer cannot be detected, a project_dir needs to be specified:
        $this->runGrumphp($this->rootDir, $this->filesystem->buildPath($projectDir, 'vendor'), [
            'GRUMPHP_PROJECT_DIR' => $projectDir,
        ]);
    }

    /** @test */
    function it_has_composer_in_root_but_grumphp_in_project_folder()
    {
        $projectDir = $this->mkdir('project');
        $composerFile = $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($projectDir);
        $this->registerGrumphpDefaultPathInComposer($composerFile, $grumphpFile);
        $this->registerGrumphpProjectPathInComposer($composerFile, $projectDir);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $projectDir);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
        $this->runGrumphp($projectDir, '../vendor');
    }

    /**
     * TODO
     *
     * Known issues:
     * - test different paths have same outputs during run (e.g. composer / grumphp in different paths)
     * - test relative grumphp path in git commit hooks:
     * - test relative paths in global environment vars (currently throws exception on Filesystem::..relative()
     *
     * Should handle:
     * - test git submodule  -> #459
     * - test git commit -a and -p
     * - test file names grumphp.yaml grumphp.yml.dist, grumphp.yaml.dist : maybe better in a paths tester though
     * - test symlinks (git dir is on another drive for example : windows)
     * - test new GRUMPHP_ environment vars
     * - test with --config param
     * - test completely insane structure
     *
     *
     * *Global / yay or nay:*
     * - test global install / or at random place is also good enough
     * - test phar ?
     *
     * * Manual for now*
     * - vagrant
     * - docker
     * - composer-bin-plugin
     *
     */
}
