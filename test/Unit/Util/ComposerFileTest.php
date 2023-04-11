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
    public function it_is_initializable(): void
    {
        $composerFile = new ComposerFile('composer.json', [
            'config' => [
                'sort-packages' => true,
            ]
        ]);
        $this->assertInstanceOf(ComposerFile::class, $composerFile);
    }

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


    /**
     * @test
     */
    public function it_knows_the_composer_file_path(): void
    {
        $composerFile = new ComposerFile('/my/location/composer.json', []);
        $this->assertSame('/my/location/composer.json', $composerFile->getPath());
    }

    /**
     * @test
     */
    public function it_should_have_a_default_config_path(): void
    {
        $composerFile = new ComposerFile(
            '/my/location/composer.json',
            [
                'config' => [
                    'sort-packages' => true,
                ]
            ]
        );
        $this->assertNull($composerFile->getConfigDefaultPath());
    }

    /**
     * @test
     */
    public function it_should_have_a_custom_config_path(): void
    {
        $composerFile = new ComposerFile(
            '/my/location/composer.json',
            [
                'config' => [
                    'sort-packages' => true,
                ],
                'extra' => [
                    'grumphp' => [
                        'config-default-path' => 'some/folder'
                    ]
                ]
            ]
        );
        $this->assertSame('some/folder', $composerFile->getConfigDefaultPath());
   }
}
