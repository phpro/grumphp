<?php
declare( strict_types=1 );

namespace GrumPHPTest\Unit\Locator;

use GrumPHP\Locator\GitRepositoryDirLocator;
use GrumPHP\Util\Filesystem;
use GrumPHPTest\Symfony\FilesystemTestCase;
use PHPUnit\Framework\Attributes\Test;

class GitRepositoryDirLocatorTest extends FilesystemTestCase
{

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var GitRepositoryDirLocator
     */
    private $locator;

    /**
     * @var string
     */
    private $gitDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem               = new Filesystem();
        $this->locator                  = new GitRepositoryDirLocator($this->filesystem);
        $this->gitDir                   = $this->workspace . DIRECTORY_SEPARATOR . '.git';
    }

    #[Test]
    public function it_can_passthrough_git_dir_path(): void
    {
        $this->filesystem->mkdir($this->gitDir);
        $this->assertEquals($this->gitDir, $this->locator->locate($this->gitDir));
    }

    #[Test]
    public function it_can_locate_submodule_git_dir(): void
    {
        $this->filesystem->dumpFile($this->gitDir, 'gitdir: ../dev/null');
        $this->assertEquals(
            $this->workspace . DIRECTORY_SEPARATOR . '../dev/null',
            $this->locator->locate($this->gitDir)
        );
    }

    #[Test]
    public function it_can_passthrough_git_dir_path_if_file_is_not_parseable(): void
    {
        $this->filesystem->dumpFile($this->gitDir, 'not parseable');
        $this->assertEquals($this->gitDir, $this->locator->locate($this->gitDir));
    }

    #[Test]
    public function it_can_locate_git_dir_in_workspaces(): void
    {
        $ourWorktreeProject = $this->workspace.'/project1/';
        $worktreeGitRoot = $this->gitDir.'/worktrees/worktree1/';
        mkdir($worktreeGitRoot, 0777, true);
        $this->filesystem->dumpFile($worktreeGitRoot.'/commondir', '../..');
        $this->filesystem->dumpFile($ourWorktreeProject.'/.git', 'gitdir: '.$this->gitDir.'/worktrees/worktree1');
        $this->assertEquals($this->gitDir, $this->locator->locate($this->gitDir));
    }

    #[Test]
    public function it_collapses_a_worktree_to_the_common_root_for_hooks(): void
    {
        [$worktreeGitFile, ] = $this->setupWorktree();

        $this->assertEquals($this->gitDir, $this->locator->locate($worktreeGitFile));
    }

    #[Test]
    public function it_locates_the_worktree_own_git_dir_for_file_listing(): void
    {
        [$worktreeGitFile, $worktreeGitDir] = $this->setupWorktree();

        $this->assertEquals($worktreeGitDir, $this->locator->locateWorktreeGitDir($worktreeGitFile));
    }

    #[Test]
    public function it_locates_worktree_git_dir_identically_to_locate_for_normal_checkout(): void
    {
        $this->filesystem->mkdir($this->gitDir);
        $this->assertEquals($this->gitDir, $this->locator->locateWorktreeGitDir($this->gitDir));
    }

    #[Test]
    public function it_locates_worktree_git_dir_identically_to_locate_for_submodule(): void
    {
        $this->filesystem->dumpFile($this->gitDir, 'gitdir: ../dev/null');
        $this->assertEquals(
            $this->workspace . DIRECTORY_SEPARATOR . '../dev/null',
            $this->locator->locateWorktreeGitDir($this->gitDir)
        );
    }

    /**
     * @return array{0: string, 1: string}  [worktree .git file, worktree own git dir]
     */
    private function setupWorktree(): array
    {
        $worktreeGitDir = $this->gitDir.DIRECTORY_SEPARATOR.'worktrees'.DIRECTORY_SEPARATOR.'worktree1';
        $this->filesystem->mkdir($worktreeGitDir);
        $this->filesystem->dumpFile($worktreeGitDir.DIRECTORY_SEPARATOR.'commondir', '../..');

        $worktreeProject = $this->workspace.DIRECTORY_SEPARATOR.'worktree-project';
        $this->filesystem->mkdir($worktreeProject);
        $worktreeGitFile = $worktreeProject.DIRECTORY_SEPARATOR.'.git';
        $this->filesystem->dumpFile($worktreeGitFile, 'gitdir: '.$worktreeGitDir);

        return [$worktreeGitFile, $worktreeGitDir];
    }
}
