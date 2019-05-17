<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

class GitHookParametersTest extends AbstractE2ETestCase
{
    /** @test */
    function it_can_specify_simple_hook_exec_command()
    {
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);

        $php = $this->useUnixDirectorySeparator($this->executableFinder->find('php'));
        $this->mergeGrumphpConfig($grumphpFile, [
            'parameters' => [
                'git_hook_variables' => [
                    'EXEC_GRUMPHP_COMMAND' => $php,
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist('{[\'"]?'.preg_quote($php, '{').'[\'"]?}i');

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
    }

    /** @test */
    function it_can_specify_hook_exec_command_with_additional_arguments()
    {
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);

        $php = $this->useUnixDirectorySeparator($this->executableFinder->find('php'));
        $this->mergeGrumphpConfig($grumphpFile, [
            'parameters' => [
                'git_hook_variables' => [
                    'EXEC_GRUMPHP_COMMAND' => $php.' -d date.timezone=Europe/Brussels',
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);

        $hookPattern = '{[\'"]?'.preg_quote($php, '{').'[\'"]? [\'"]?-d[\'"]? [\'"]?date\.timezone=Europe/Brussels[\'"]?}';
        $this->ensureHooksExist($hookPattern);

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
    }
}
