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
     * @var GrumPHP|ObjectProphecy
     */
    private $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = $this->prophesize(GrumPHP::class);
        $this->filesystem = new Filesystem($this->config->reveal());

        $this->config->getConfigFile()->willReturn($this->buildPath('grumphp.yml'));
        $this->config->getGitDir()->willReturn($this->workspace);
    }

    /** @test */
    public function it_extends_symfony_filesystem(): void
    {
        $this->assertInstanceOf(SymfonyFilesystem::class, $this->filesystem);
    }

    /** @test */
    public function it_can_load_file_contents(): void
    {
        $file = $this->workspace.DIRECTORY_SEPARATOR.'helloworld.txt';
        file_put_contents($file, $content = 'hello world');

        $this->assertEquals(
            $content,
            $this->filesystem->readFromFileInfo(new \SplFileInfo($file))
        );
    }

    /** @test */
    public function it_knows_the_git_directory(): void
    {
        $this->assertSame(
            $this->workspace,
            $this->filesystem->getGitDir()
        );
    }

    /** @test */
    public function it_knows_the_project_directory(): void
    {
        $this->assertSame(
            $this->workspace,
            $this->filesystem->getProjectDir()
        );
    }

    /**
     * @test
     * @dataProvider provideRelativeProjectDirCases
     */
    public function it_can_load_relative_project_dir_path(
        string $projectDir,
        string $gitDir,
        string $expected,
        bool $forceGitDirAsAbsolute = false
    ): void {
        $this->filesystem->mkdir($this->buildPath($projectDir));
        $this->config->getConfigFile()->willReturn($this->buildPath($projectDir.'/grumphp.yml'));
        $this->config->getGitDir()->willReturn($forceGitDirAsAbsolute ? $this->buildPath($gitDir) : $gitDir);

        $this->assertSame($expected, $this->filesystem->getRelativeProjectDir());
    }

    public function provideRelativeProjectDirCases(): array
    {
        return [
            [
                '',
                '.',
                './'
            ],
            [
                '',
                '',
                './',
                true
            ],
            [
                'project',
                '..',
                'project/'
            ],
            [
                'project',
                '',
                'project/',
                true
            ],
            [
                'project/hello/world',
                '../..',
                'hello/world/'
            ],
        ];
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

    /**
     * @test
     * @dataProvider provideFilenamesRelativeToProjectDirCases()
     */
    public function it_can_make_file_paths_relative_to_project_dir($projectDir, $path, $expected): void
    {
        $this->filesystem->mkdir($this->buildPath($projectDir));
        $this->config->getConfigFile()->willReturn($this->buildPath($projectDir.'/grumphp.yml'));

        $result = $this->filesystem->makePathRelativeToProjectDir($path);

        $this->assertSame($expected, $result);
    }

    public function provideFilenamesRelativeToProjectDirCases()
    {
        return [
            [
                'project',
                'project/somefile',
                'somefile',
            ],
            [
                'project',
                'project/somefile/somewhere',
                'somefile/somewhere',
            ],
            [
                'project',
                'somefile',
                'somefile'
            ],
            [
                '',
                'somefile',
                'somefile'
            ],
            [
                '',
                'somefile/somewhere/sometime',
                'somefile/somewhere/sometime'
            ]
        ];
    }

    private function buildPath(string $path): string
    {
        return $this->workspace.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
