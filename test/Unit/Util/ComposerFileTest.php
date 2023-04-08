<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Util;

use GrumPHP\Util\ComposerFile;
use PHPUnit\Framework\TestCase;

class ComposerFileTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_detect_the_bin_dir_using_the_bindir_configuration(): void
    {
        $composerFile = new ComposerFile('composer.json', [
            'config' => [
                'bin-dir' => 'alternative/bin',
            ],
        ]);
        $this->assertSame('alternative/bin', $composerFile->getBinDir());
    }

    /**
     * @test
     */
    public function it_can_detect_the_bin_dir_using_the_vendordir_configuration(): void
    {
        $composerFile = new ComposerFile('composer.json', [
            'config' => [
                'vendor-dir' => 'alternative/vendor',
            ],
        ]);
        $this->assertSame('alternative/vendor/bin', $composerFile->getBinDir());
    }

    /**
     * @test
     */
    public function it_can_detect_the_bin_dir_using_the_default_configuration(): void
    {
        $composerFile = new ComposerFile('composer.json', []);
        $this->assertSame('vendor/bin', $composerFile->getBinDir());
    }
}
