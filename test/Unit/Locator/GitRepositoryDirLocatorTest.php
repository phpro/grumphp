<?php
declare( strict_types=1 );

namespace GrumPHPTest\Unit\Locator;

use GrumPHP\Locator\GitRepositoryDirLocator;
use GrumPHP\Util\Filesystem;
use GrumPHPTest\Symfony\FilesystemTestCase;

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

    /**
     * @test
     */
    public function it_can_passthrough_git_dir_path(): void
    {
        $this->filesystem->mkdir($this->gitDir);
        $this->assertEquals($this->gitDir, $this->locator->locate($this->gitDir));
    }

    /**
     * @test
     */
    public function it_can_locate_submodule_git_dir(): void
    {
        $this->filesystem->dumpFile($this->gitDir, 'gitdir: ../dev/null');
        $this->assertEquals(
            $this->workspace . DIRECTORY_SEPARATOR . '../dev/null',
            $this->locator->locate($this->gitDir)
        );
    }

    /**
     * @test
     */
    public function it_can_passthrough_git_dir_path_if_file_is_not_parseable(): void
    {
        $this->filesystem->dumpFile($this->gitDir, 'not parseable');
        $this->assertEquals($this->gitDir, $this->locator->locate($this->gitDir));
    }

    /**
     * @test
     */
    public function it_can_locate_git_dir_in_workspaces(): void
    {
        $worktreeRoot = $this->workspace.'/git_root/worktrees/git_worktree/';
        mkdir($worktreeRoot, 0777, true);
        $this->filesystem->dumpFile($worktreeRoot.'/commondir', '../..');
        $this->filesystem->dumpFile($this->gitDir, 'gitdir: '.$this->workspace.'/git_root/worktrees/git_worktree');
        $this->assertEquals($this->workspace.DIRECTORY_SEPARATOR.'git_root', $this->locator->locate($this->gitDir));
    }
}
