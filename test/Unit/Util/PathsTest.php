<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Util;

use GrumPHP\Configuration\GuessedPaths;
use GrumPHP\Util\ComposerFile;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use GrumPHPTest\Symfony\FilesystemTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\PhpUnit\ProphecyTrait;

class PathsTest extends FilesystemTestCase
{
    use ProphecyTrait;

    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var GuessedPaths
     */
    private $guessedPaths;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $packageRootDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem();

        $this->guessedPaths = new GuessedPaths(
            $this->workspace,
            $this->buildPath($this->workspace, '.git'),
            $this->workspace,
            $this->workspace,
            $this->buildPath($this->workspace, 'vendor/bin'),
            new ComposerFile($this->buildPath($this->workspace, 'composer.json'), []),
            $this->buildPath($this->workspace, 'grumphp.json')
        );

        $this->paths = new Paths($this->filesystem, $this->guessedPaths);
        $this->packageRootDir = dirname(__DIR__, 3);
    }

    #[Test]
    public function it_knows_package_root_dir(): void
    {
        $this->assertSame(
            $this->packageRootDir,
            $this->paths->getGrumPHPExecutableRootDir()
        );
    }

    #[Test]
    public function it_knows_internal_resources_dir(): void
    {
        $this->assertSame(
            $this->buildPath($this->packageRootDir, 'resources'),
            $this->paths->getInternalResourcesDir()
        );
    }

    #[Test]
    public function it_knows_internal_ascii_dir(): void
    {
        $this->assertSame(
            $this->buildPath($this->packageRootDir, 'resources/ascii'),
            $this->paths->getInternalAsciiPath()
        );
    }

    #[Test]
    public function it_knows_internal_hooks_template_dir(): void
    {
        $this->assertSame(
            $this->buildPath($this->packageRootDir, 'resources/hooks'),
            $this->paths->getInternalGitHookTemplatesPath()
        );
    }

    #[Test]
    public function it_knows_the_git_working_directory(): void
    {
        $this->assertSame(
            $this->guessedPaths->getGitWorkingDir(),
            $this->paths->getGitWorkingDir()
        );
    }

    #[Test]
    public function it_knows_the_git_repository_directory(): void
    {
        $this->assertSame(
            $this->guessedPaths->getGitRepositoryDir(),
            $this->paths->getGitRepositoryDir()
        );
    }

    #[Test]
    public function it_knows_the_git_hooks_directory(): void
    {
        $this->assertSame(
            $this->buildPath($this->guessedPaths->getGitRepositoryDir(), 'hooks'),
            $this->paths->getGitHooksDir()
        );
    }

    #[Test]
    public function it_knows_the_git_worktree_directory(): void
    {
        $this->assertSame(
            $this->guessedPaths->getGitWorktreeDir(),
            $this->paths->getGitWorktreeDir()
        );
    }

    #[Test]
    public function it_separates_the_worktree_dir_from_the_hooks_dir_inside_a_worktree(): void
    {
        $guessedPaths = new GuessedPaths(
            $this->workspace,
            $common = $this->buildPath($this->workspace, '.git'),
            $this->workspace,
            $this->workspace,
            $this->buildPath($this->workspace, 'vendor/bin'),
            new ComposerFile($this->buildPath($this->workspace, 'composer.json'), []),
            $this->buildPath($this->workspace, 'grumphp.json'),
            $worktree = $this->buildPath($this->workspace, '.git/worktrees/wt1')
        );
        $paths = new Paths($this->filesystem, $guessedPaths);

        $this->assertSame($worktree, $paths->getGitWorktreeDir());
        $this->assertSame($common, $paths->getGitRepositoryDir());
        $this->assertSame($this->buildPath($common, 'hooks'), $paths->getGitHooksDir());
    }

    #[Test]
    public function it_knows_the_project_directory(): void
    {
        $this->assertSame(
            $this->workspace,
            $this->paths->getProjectDir()
        );
    }

    #[DataProvider('provideFilenamesRelativeToProjectDirCases')]
    #[Test]
    public function it_can_make_file_paths_relative_to_project_dir($projectDir, $path, $expected): void
    {
        $guessedPaths = $this->prophesize(GuessedPaths::class);
        $guessedPaths->getGitWorkingDir()->willReturn($this->workspace);
        $guessedPaths->getProjectDir()->willReturn($this->buildPath($this->workspace, $projectDir));
        $this->guessedPaths = $guessedPaths->reveal();
        $this->paths = new Paths($this->filesystem, $this->guessedPaths);

        // Create dirs:
        $this->filesystem->mkdir($this->buildPath($this->workspace, $projectDir));
        $this->filesystem->mkdir($this->buildPath($this->workspace, $path));

        // Try both relative + absolute paths
        $this->assertSame($expected, $this->paths->makePathRelativeToProjectDir($path));
        $this->assertSame($expected, $this->paths->makePathRelativeToProjectDir($this->buildPath($this->workspace, $path)));
    }

    public static function provideFilenamesRelativeToProjectDirCases()
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

    #[DataProvider('provideFilenamesRelativeToProjectDirWhenInProjectPathCases')]
    #[Test]
    public function it_can_make_file_paths_relative_to_project_dir_when_in_project_folder(
        $projectDir,
        $path,
        $expected,
        bool $shortened
    ): void {
        $guessedPaths = $this->prophesize(GuessedPaths::class);
        $guessedPaths->getGitWorkingDir()->willReturn($this->workspace);
        $guessedPaths->getProjectDir()->willReturn($this->buildPath($this->workspace, $projectDir));
        $this->guessedPaths = $guessedPaths->reveal();
        $this->paths = new Paths($this->filesystem, $this->guessedPaths);

        // Create dirs:
        $this->filesystem->mkdir($fullProjectPath = $this->buildPath($this->workspace, $projectDir));
        $this->filesystem->mkdir($fullPath = $this->buildPath($this->workspace, $path));

        $this->assertSame(
            $shortened ? $expected : $fullPath,
            $this->paths->makePathRelativeToProjectDirWhenInSubFolder(
                $this->buildPath($this->workspace, $path)
            )
        );
    }

    public static function provideFilenamesRelativeToProjectDirWhenInProjectPathCases()
    {
        return [
            [
                'project',
                'project/somefile',
                'somefile',
                true,
            ],
            [
                'project',
                'project/somefile/somewhere',
                'somefile/somewhere',
                true,
            ],
            [
                'project',
                'somefile',
                'somefile',
                false,
            ],
            [
                'project',
                'somefile',
                'somefile',
                false,
            ],
            [
                'project',
                'somefile/somewhere/sometime',
                'somefile/somewhere/sometime',
                false
            ]
        ];
    }

    private function buildPath(string $basePath, string $path): string
    {
        return $basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
