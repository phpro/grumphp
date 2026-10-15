<?php

namespace spec\GrumPHP\Locator;

use Gitonomy\Git\Diff\Diff;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Git\GitRepository;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use GrumPHPTest\Helpers\TemporaryGitRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangedFilesSpec extends ObjectBehavior
{
    /**
     * @var TemporaryGitRepository
     */
    private $gitRepository;

    function let(GitRepository $repository, Filesystem $filesystem, Paths $paths)
    {
        $this->gitRepository = TemporaryGitRepository::create();
        $repository->getWorkingCopy()->willReturn($this->gitRepository->getRepository()->getWorkingCopy());

        $this->beConstructedWith($repository, $filesystem, $paths);
        $paths->makePathRelativeToProjectDir(Argument::type('string'))->will(
            function (array $arguments): string {
                return $arguments[0];
            }
        );
    }

    function letGo()
    {
        $this->gitRepository->remove();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChangedFiles::class);
    }

    function it_will_list_all_diffed_files(Filesystem $filesystem)
    {
        $this->gitRepository->dumpFile('file1.txt', 'changed');
        $this->gitRepository->dumpFile('moved.txt', 'moved');
        $this->gitRepository->dumpFile('file3.txt', 'deleted');
        $this->gitRepository->git('add', '--all');
        $this->gitRepository->git('commit', '--quiet', '--message=Initial commit');
        $this->gitRepository->dumpFile('file1.txt', 'changed again');
        $this->gitRepository->git('mv', 'moved.txt', 'file2.txt');
        $this->gitRepository->git('rm', '--quiet', 'file3.txt');
        $this->gitRepository->git('add', '--all');

        $filesystem->exists('file1.txt')->willReturn(true);
        $filesystem->exists('file2.txt')->willReturn(true);
        $filesystem->exists('file3.txt')->willReturn(false);

        $result = $this->locateFromGitRepository();
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result[0]->getPathname()->shouldBe('file1.txt');
        $result[1]->getPathname()->shouldBe('file2.txt');
        $result->getIterator()->count()->shouldBe(2);
    }

    function it_will_not_list_non_existing_files(Filesystem $filesystem)
    {
        $this->gitRepository->dumpFile('file1.txt', 'changed');
        $this->gitRepository->git('add', '--all');

        $filesystem->exists('file1.txt')->willReturn(false);

        $result = $this->locateFromGitRepository();
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->getIterator()->count()->shouldBe(0);
    }

    function it_will_list_all_diffed_files_from_raw_diff_input(GitRepository $repository, Filesystem $filesystem)
    {
        $rawDiff = ":000000 100644 0000000 d95f3ad A\tfile.txt

diff --git a/file.txt b/file.txt
new file mode 100644
index 0000000000000000000000000000000000000000..d95f3ad14dee633a758d2e331151e950dd13e4ed
--- /dev/null
+++ b/file.txt
@@ -0,0 +1 @@
+content
";

        $repository->createRawDiff($rawDiff)->willReturn(Diff::parse($rawDiff));
        $filesystem->exists('file.txt')->willReturn(true);

        $result = $this->locateFromRawDiffInput($rawDiff);
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result[0]->getPathname()->shouldBe('file.txt');
        $result->getIterator()->count()->shouldBe(1);
    }
}
