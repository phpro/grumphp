<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Util;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Util\Filesystem;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Filesystem\Tests\FilesystemTestCase;

class FilesystemTest extends FilesystemTestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem();
    }

    /** @test */
    public function it_extends_symfony_filesystem(): void
    {
        $this->assertInstanceOf(SymfonyFilesystem::class, $this->filesystem);
    }

    /** @test */
    public function it_can_load_file_contents(): void
    {
        $file = $this->buildPath('helloworld.txt');
        file_put_contents($file, $content = 'hello world');

        $this->assertEquals(
            $content,
            $this->filesystem->readFromFileInfo(new \SplFileInfo($file))
        );
    }

    /** @test */
    public function it_knows_an_item_is_a_file(): void
    {
        $file = $this->buildPath('helloworld.txt');
        $folder = $this->buildPath('folder');

        $this->filesystem->touch($file);
        $this->filesystem->mkdir($folder);

        $this->assertTrue($this->filesystem->isFile($file));
        $this->assertFalse($this->filesystem->isFile($folder));
    }

    /** @test */
    public function it_can_load_file_paths(): void
    {
        $file = $this->buildPath('helloworld.txt');
        file_put_contents($file, $content = 'hello world');

        $this->assertEquals(
            $content,
            $this->filesystem->readPath($file)
        );
    }

    /** @test */
    public function it_can_make_paths_absolute(): void
    {
        $file = $this->buildPath($fileName = 'somefile.txt');
        $this->filesystem->touch($file);

        $this->assertSame($this->filesystem->makePathAbsolute($fileName, $this->workspace), $file);
        $this->assertSame($this->filesystem->makePathAbsolute($file, $this->workspace), $file);
    }

    /**
     * @test
     * @dataProvider provideRealpathTestcases
     */
    public function it_can_not_convert_paths_to_real_paths(string $path): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->filesystem->realpath($this->buildPath($path));
    }

    /**
     * @test
     * @dataProvider provideRealpathTestcases
     */
    public function it_can_convert_paths_to_real_paths(string $path, bool $isFile): void
    {
        $workspacePath = $this->buildPath($path);
        $method = $isFile ? 'touch' : 'mkdir';
        $assertion = $isFile ? 'assertFileExists' : 'assertDirectoryExists';
        $this->filesystem->{$method}($workspacePath);

        $realpath = $this->filesystem->realpath($workspacePath);
        $this->{$assertion}($realpath);
    }

    public function provideRealpathTestcases()
    {
        return [
            [
                'file.txt',
                true,
            ],
            [
                './file.txt',
                true,
            ],
            [
                'directory',
                false,
            ],
            [
                './directory',
                false,
            ]
        ];
    }

    /** @test */
    public function it_can_build_paths(): void
    {
        $this->assertSame(
            $this->buildPath('hello.txt'),
            $this->filesystem->buildPath($this->workspace, 'hello.txt')
        );
    }

    /**
 * @test
 * @dataProvider provideGuessedFiles
 */
    public function it_can_guess_files(callable $setupWorkspace, array $paths, array $fileNames, string $expected)
    {
        $setupWorkspace($this->filesystem, $this->workspace);

        $this->assertSame(
            $this->buildPath($expected),
            $this->filesystem->guessFile(
                array_map(
                    function ($path) {
                        return rtrim($this->buildPath($path), '/\\');
                    },
                    $paths
                ),
                $fileNames
            )
        );
    }

    /**
     * @test
     * @dataProvider provideGuessedPaths
     */
    public function it_can_guess_paths(callable $setupWorkspace, array $paths, string $expected)
    {
        $setupWorkspace($this->filesystem, $this->workspace);

        $this->assertSame(
            $expected ? $this->buildPath($expected) : $this->workspace,
            $this->filesystem->guessPath(
                array_map(
                    function ($path) {
                        return rtrim($this->buildPath($path), '/\\');
                    },
                    $paths
                )
            )
        );
    }

    /** @test */
    public function it_knows_if_a_path_is_in_a_folder(): void
    {
        $this->filesystem->mkdir($projectDir = $this->buildPath('project'));
        $this->filesystem->mkdir($composerDir = $this->buildPath('composer'));
        $this->filesystem->mkdir($composerSubDir = $this->buildPath('composer/subdir'));
        $this->filesystem->touch($composerJson = $this->buildPath('composer/composer.json'));

        $this->assertTrue($this->filesystem->isPathInFolder($projectDir, $this->workspace));
        $this->assertTrue($this->filesystem->isPathInFolder($composerDir, $this->workspace));
        $this->assertTrue($this->filesystem->isPathInFolder($composerJson, $this->workspace));
        $this->assertTrue($this->filesystem->isPathInFolder($composerJson, $composerDir));
        $this->assertTrue($this->filesystem->isPathInFolder($composerSubDir, $composerDir));
        $this->assertTrue($this->filesystem->isPathInFolder($composerSubDir, $this->workspace));

        $this->assertFalse($this->filesystem->isPathInFolder($projectDir, $composerDir));
        $this->assertFalse($this->filesystem->isPathInFolder($projectDir, $composerSubDir));
        $this->assertFalse($this->filesystem->isPathInFolder($composerSubDir, $projectDir));
        $this->assertFalse($this->filesystem->isPathInFolder($composerDir, $projectDir));
        $this->assertFalse($this->filesystem->isPathInFolder($composerJson, $projectDir));
        $this->assertFalse($this->filesystem->isPathInFolder($this->workspace, $projectDir));
        $this->assertFalse($this->filesystem->isPathInFolder($this->workspace, $composerDir));
        $this->assertFalse($this->filesystem->isPathInFolder($this->workspace, $composerSubDir));
    }

    public function provideGuessedFiles()
    {
        yield [
            static function(Filesystem $filesystem, string $workspace) {
                $filesystem->touch($filesystem->buildPath($workspace, 'grumphp.yml'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'second'));
                $filesystem->touch($filesystem->buildPath($workspace, 'second/grumphp.yml'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third'));
            },
            [
                '',
                'second',
                'third',
            ],
            [
                'grumphp.yml',
            ],
            'grumphp.yml',
        ];

        yield [
            static function(Filesystem $filesystem, string $workspace) {
                $filesystem->touch($filesystem->buildPath($workspace, 'grumphp.yml'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'second'));
                $filesystem->touch($filesystem->buildPath($workspace, 'second/grumphp.yml'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third'));
            },
            [
                'third',
                'second',
                '',
            ],
            [
                'grumphp.yml',
            ],
            'second/grumphp.yml',
        ];

        yield [
            static function(Filesystem $filesystem, string $workspace) {
                $filesystem->touch($filesystem->buildPath($workspace, 'grumphp.yml'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'second'));
                $filesystem->touch($filesystem->buildPath($workspace, 'second/grumphp.yml.dist'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third'));
            },
            [
                'third',
                'second',
                '',
            ],
            [
                'grumphp.yml',
                'grumphp.yml.dist',
            ],
            'second/grumphp.yml.dist',
        ];

        yield [
            static function(Filesystem $filesystem, string $workspace) {
                $filesystem->touch($filesystem->buildPath($workspace, 'grumphp.yml'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'second'));
                $filesystem->touch($filesystem->buildPath($workspace, 'second/grumphp.yml'));
                $filesystem->touch($filesystem->buildPath($workspace, 'second/grumphp.yml.dist'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third'));
            },
            [
                'third',
                'second/grumphp.yml.dist',
                '',
            ],
            [
                'grumphp.yml',
                'grumphp.yml.dist',
            ],
            'second/grumphp.yml.dist',
        ];

        yield [
            static function(Filesystem $filesystem, string $workspace) {
            },
            [
                'third',
                'second/grumphp.yml.dist',
                '',
            ],
            [
                'grumphp.yml',
                'grumphp.yml.dist',
            ],
            'third/grumphp.yml',
        ];

        yield [
            static function(Filesystem $filesystem, string $workspace) {
            },
            [
                'file/grumphp.yml',
                'second/grumphp.yml.dist',
                '',
            ],
            [
                'grumphp.yml',
                'grumphp.yml.dist',
            ],
            'file/grumphp.yml',
        ];
    }

    public function provideGuessedPaths()
    {
        yield [
            static function(Filesystem $filesystem, string $workspace) {
                $filesystem->mkdir($filesystem->buildPath($workspace, 'second'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third/fourth'));
            },
            [
                '',
                'second',
                'third',
            ],
            '',
        ];

        yield [
            static function(Filesystem $filesystem, string $workspace) {
                $filesystem->mkdir($filesystem->buildPath($workspace, 'second'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third/fourth'));
            },
            [
                'doesnotexist',
                'third/fourth',
                'third',
            ],
            'third/fourth',
        ];

        yield [
            static function(Filesystem $filesystem, string $workspace) {
                $filesystem->mkdir($filesystem->buildPath($workspace, 'second'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third'));
                $filesystem->mkdir($filesystem->buildPath($workspace, 'third/fourth'));
            },
            [
                'second',
                'third',
            ],
            'second',
        ];
    }

    private function buildPath(string $path): string
    {
        return $this->workspace.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
