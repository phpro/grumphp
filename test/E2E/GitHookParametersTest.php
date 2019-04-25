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

        $this->mergeGrumphpConfig($grumphpFile, [
            'parameters' => [
                'git_hook_variables' => [
                    'EXEC_GRUMPHP_COMMAND' => $this->executableFinder->find('php')
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
    }

    /** @test */
    function it_can_specify_hook_exec_command_with_additional_arguments()
    {
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);

        $this->mergeGrumphpConfig($grumphpFile, [
            'parameters' => [
                'git_hook_variables' => [
                    'EXEC_GRUMPHP_COMMAND' => $this->executableFinder->find('php').' -d date.timezone=Europe/Brussels'
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
    }
}
