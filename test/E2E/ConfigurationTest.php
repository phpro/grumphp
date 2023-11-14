<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

class ConfigurationTest extends AbstractE2ETestCase
{
    /** @test */
    function it_should_be_able_to_resolve_env_variable_in_configuration()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig(path: $this->rootDir, customConfig: [
            'parameters' => [
                'env(GRUMPHP_PARAMETER_TEST)' => '~'
            ],
            'grumphp' => [
                'ascii' => [
                    'succeeded' => '%env(string:GRUMPHP_PARAMETER_TEST)%',
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
        $process = $this->runGrumphp(projectPath: $this->rootDir, environment: [
            'GRUMPHP_PARAMETER_TEST' => 'succeeded.txt',
        ]);

        $this->assertStringContainsString(
            file_get_contents(PROJECT_BASE_PATH . '/resources/ascii/succeeded.txt'),
            $process->getOutput(),
        );
    }
}
