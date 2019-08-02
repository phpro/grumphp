<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Util;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Util\ComposerFile;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Filesystem\Tests\FilesystemTestCase;

class PathsTest extends FilesystemTestCase
{
    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var GrumPHP|ObjectProphecy
     */
    private $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $packageRootDir;

    protected function setUp()
    {
        parent::setUp();
        $this->config = $this->prophesize(GrumPHP::class);
        $this->filesystem = new Filesystem();

        $this->config->getConfigFile()->willReturn($this->buildPath($this->workspace, 'grumphp.yml'));
        $this->config->getGitDir()->willReturn($this->workspace);
        $this->config->getWorkingDir()->willReturn($this->workspace);
        $this->config->getComposerFile()->willReturn(
            new ComposerFile($this->buildPath($this->workspace, 'composer.json'), [])
        );

        $this->paths = new Paths($this->filesystem, $this->config->reveal());
        $this->packageRootDir = dirname(__DIR__, 3);
    }

    /** @test */
    public function it_knows_package_root_dir(): void
    {
        $this->assertSame(
            $this->packageRootDir,
            $this->paths->getGrumPHPExecutableRootDir()
        );
    }

    /** @test */
    public function it_knows_internal_resources_dir(): void
    {
        $this->assertSame(
            $this->buildPath($this->packageRootDir, 'resources'),
            $this->paths->getInternalResourcesDir()
        );
    }

    /** @test */
    public function it_knows_internal_ascii_dir(): void
    {
        $this->assertSame(
            $this->buildPath($this->packageRootDir, 'resources/ascii'),
            $this->paths->getInternalAsciiPath()
        );
    }

    /** @test */
    public function it_knows_internal_hooks_template_dir(): void
    {
        $this->assertSame(
            $this->filesystem->buildPath($this->packageRootDir, 'resources/hooks'),
            $this->paths->getInternalGitHookTemplatesPath()
        );
    }

    /** @test */
    public function it_knows_the_grumphp_config_directory(): void
    {
        $this->assertSame(
            $this->workspace,
            $this->paths->getGrumPHPConfigDir()
        );
    }

    /** @test */
    public function it_knows_the_composer_config_directory(): void
    {
        $this->assertSame(
            $this->workspace,
            $this->paths->getComposerConfigDir()
        );
    }

    /** @test */
    public function it_knows_the_git_directory(): void
    {
        $this->assertSame(
            $this->workspace,
            $this->paths->getGitDir()
        );
    }

    /** @test */
    public function it_knows_the_working_directory(): void
    {
        $this->assertSame(
            $this->workspace,
            $this->paths->getWorkingDir()
        );
    }

    /**
     * @test
     * @dataProvider provideFilenamesRelativeToProjectDirCases()
     */
    public function it_can_make_file_paths_relative_to_project_dir($projectDir, $path, $expected): void
    {
        $this->filesystem->mkdir($this->buildPath($this->workspace, $projectDir));
        $this->config->getConfigFile()->willReturn($this->buildPath($this->workspace, $projectDir.'/grumphp.yml'));

        $result = $this->paths->makePathRelativeToProjectDir($path);

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

    private function buildPath(string $basePath, string $path): string
    {
        return $basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
