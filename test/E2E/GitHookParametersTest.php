<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

class GitHookParametersTest extends AbstractE2ETestCase
{
    /** @test */
    function it_can_specify_simple_hook_exec_command()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);

        $php = $this->useUnixDirectorySeparator($this->executableFinder->find('php'));
        $this->mergeGrumphpConfig($grumphpFile, [
            'grumphp' => [
                'git_hook_variables' => [
                    'EXEC_GRUMPHP_COMMAND' => $php,
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist($this->rootDir, '{[\'"]?'.preg_quote($php, '{').'[\'"]?}i');

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
    }

    /** @test */
    function it_can_specify_hook_exec_command_with_additional_arguments()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);

        $php = $this->useUnixDirectorySeparator($this->executableFinder->find('php'));
        $this->mergeGrumphpConfig($grumphpFile, [
            'grumphp' => [
                'git_hook_variables' => [
                    'EXEC_GRUMPHP_COMMAND' => [$php, '-d date.timezone=Europe/Brussels'],
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);

        $hookPattern = sprintf('{[\'"]%s[\'"] [\'"]-d date\.timezone=Europe/Brussels[\'"]}', preg_quote($php));
        $this->ensureHooksExist($this->rootDir, $hookPattern);

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
    }

    /** @test */
    function it_can_add_hook_variables()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);

        $this->mergeGrumphpConfig($grumphpFile, [
            'grumphp' => [
                'git_hook_variables' => [
                    'ENV' => [
                        'VAR1' => 'CONTENT1',
                        'VAR2' => 'CONTENT2',
                    ],
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);

        $this->ensureHooksExist($this->rootDir, '{export VAR1=CONTENT1}');
        $this->ensureHooksExist($this->rootDir, '{export VAR2=CONTENT2}');

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
    }
}
