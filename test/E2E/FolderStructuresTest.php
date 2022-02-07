<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

class FolderStructuresTest extends AbstractE2ETestCase
{
    /** @test */
    function it_should_be_able_to_load_cli_info_when_no_git_repo()
    {
        $this->initializeComposer($this->rootDir);
        $this->initializeGrumphpConfig($this->rootDir);
        $this->installComposer($this->rootDir, ['--no-plugins']);
        $this->runGrumphpInfo($this->rootDir);
    }

    /** @test */
    function it_has_all_config_files_in_root_git_dir()
    {
        $this->initializeGitInRootDir();
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
        $this->initializeGitInRootDir();
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
        $this->initializeGitInRootDir();
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
    function it_has_convention_at_another_location_through_cli_params()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $conventionsDir = $this->mkdir('vendor/phpro/conventions');
        $grumphpFile = $this->initializeGrumphpConfig($conventionsDir);
        $grumphpConfigValue = 'vendor/phpro/conventions/grumphp.yml';

        $this->installComposer($this->rootDir, ['--no-plugins']);
        $this->initializeGrumphpGitHooksWithConfig($grumphpFile);
        $this->ensureHooksExist($this->rootDir, '#--config=(\.[\/])?'.preg_quote($grumphpConfigValue, '#').'#');

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
        $this->runGrumphpWithConfig($this->rootDir, $grumphpFile);
    }

    /** @test */
    function it_has_grumphp_in_root_but_composer_in_project_folder()
    {
        $this->markTestSkipped('Broken test... Unable to locate autoloader!');

        $this->initializeGitInRootDir();
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
        $this->markTestSkipped('Broken test... Unable to locate autoloader!');

        $this->initializeGitInRootDir();
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

    /** @test */
    public function it_can_manipulate_guessed_paths_by_environment_variables_for_mega_insane_project_structures(): void
    {
        $gitDir = $this->mkdir('git');
        $this->initializeGit($gitDir);

        $projectDir = $this->mkdir($this->filesystem->buildPath($gitDir, 'project'));
        $composerDir = $this->mkdir('composer');
        $binDir = $this->mkdir('composer/bin');
        $configDir = $this->mkdir('config');
        $grumphpFile = $this->initializeGrumphpConfig($configDir);

        $composerFile = $this->initializeComposer($composerDir);
        $this->mergeComposerConfig($composerFile, ['config' => ['bin-dir' => 'bin']]);
        $this->installComposer($composerDir, ['--no-plugins']);

        $this->enableValidatePathsTask($grumphpFile, $projectDir);
        $this->runGrumphp($this->rootDir, $composerDir, [
            'GRUMPHP_PROJECT_DIR' => $projectDir,
            'GRUMPHP_GIT_WORKING_DIR' => $gitDir,
            'GRUMPHP_GIT_REPOSITORY_DIR' => $this->filesystem->buildPath($gitDir, '.git'),
            'GRUMPHP_COMPOSER_DIR' => $composerDir,
            'GRUMPHP_BIN_DIR' => $binDir,
        ]);

        // Also test relative paths
        $this->runGrumphp($this->rootDir, $composerDir, [
            'GRUMPHP_PROJECT_DIR' => $this->useCorrectDirectorySeparator('git/project'),
            'GRUMPHP_GIT_WORKING_DIR' => $this->useCorrectDirectorySeparator('git'),
            'GRUMPHP_GIT_REPOSITORY_DIR' => $this->useCorrectDirectorySeparator('git/.git'),
            'GRUMPHP_COMPOSER_DIR' => $this->useCorrectDirectorySeparator('composer'),
            'GRUMPHP_BIN_DIR' => $this->useCorrectDirectorySeparator('composer/bin'),
        ]);
    }


    /** @test */
    function it_can_deal_with_symlinks()
    {
        $sourceLocation = $this->mkdir('project-src');
        $linkLocation = $this->filesystem->buildPath($this->rootDir, 'project-linked');
        $this->filesystem->symlink($sourceLocation, $linkLocation, true);

        $this->initializeGit($linkLocation);
        $this->appendToGitignore($linkLocation);
        $this->initializeComposer($linkLocation);
        $grumphpFile = $this->initializeGrumphpConfig($linkLocation);
        $this->installComposer($linkLocation);
        $this->ensureHooksExist($linkLocation);

        $this->enableValidatePathsTask($grumphpFile, $linkLocation);

        $this->commitAll($linkLocation);
        $this->runGrumphp($linkLocation);
    }
}
