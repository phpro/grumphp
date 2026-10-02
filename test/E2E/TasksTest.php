<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

use PHPUnit\Framework\Attributes\Test;

class TasksTest extends AbstractE2ETestCase
{
    #[Test]
    function it_can_configure_a_task_under_an_alias()
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

    #[Test]
    function it_can_resolve_task_config_With_env_vars()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig(path: $this->rootDir, customConfig: [
            'grumphp' => [
                'environment' => [
                    'variables' => [
                        'SHOULD_DUMMY_SUCCEED' => 1,
                    ]
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableDummyTask($grumphpFile, $this->rootDir, [
            'should_succeed' => '%env(bool:SHOULD_DUMMY_SUCCEED)%',
        ]);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }

    #[Test]
    function it_passes_option_like_file_names_to_external_commands_as_paths()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidateArgvPathsTask($grumphpFile, $this->rootDir);
        $this->dumpOptionLikeFiles();

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }

    #[Test]
    function it_finds_blacklisted_keywords_in_option_like_file_names()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->mergeGrumphpConfig($grumphpFile, [
            'grumphp' => [
                'tasks' => [
                    'git_blacklist' => [
                        'keywords' => ['blacklisted_keyword'],
                    ],
                ],
            ],
        ]);
        $this->dumpOptionLikeFiles();
        $this->dumpFile($this->rootDir.'/-dash.php', '<?php // blacklisted_keyword'.PHP_EOL);

        try {
            $this->commitAll();
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('You have blacklisted keywords in your commit', $e->getMessage());
            $this->assertStringContainsString('-dash.php', $e->getMessage());

            return;
        }

        $this->fail('Expected git_blacklist to find the keyword in -dash.php.');
    }

    private function dumpOptionLikeFiles(): void
    {
        $this->dumpFile($this->rootDir.'/--option-like=value.php', '<?php'.PHP_EOL);
        $this->dumpFile($this->rootDir.'/-dash.php', '<?php'.PHP_EOL);
        $this->mkdir($this->rootDir.'/-dir');
        $this->dumpFile($this->rootDir.'/-dir/file.php', '<?php'.PHP_EOL);
    }
}
