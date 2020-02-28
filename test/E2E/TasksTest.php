<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

class TasksTest extends AbstractE2ETestCase
{
    /** @test */
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
}
