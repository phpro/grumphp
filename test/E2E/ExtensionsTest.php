<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

class ExtensionsTest extends AbstractE2ETestCase
{
    /** @test */
    function it_can_configure_an_extension()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableCustomExtension($grumphpFile, $this->rootDir);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }
}
