<?php

namespace spec\GrumPHP\Locator;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use Gitonomy\Git\WorkingCopy;
use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Util\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Prophet;

class ChangedFilesSpec extends ObjectBehavior
{
    function let(Repository $repository, Filesystem $filesystem)
    {
        $this->beConstructedWith($repository, $filesystem);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ChangedFiles::class);
    }

    protected function mockFile($name, $isRename = false, $isDelete = false)
    {
        $prophet = new Prophet();
        $file = $prophet->prophesize(File::class);
        $file->getName()->willReturn($name);
        $file->getNewName()->willReturn($name);
        $file->isRename()->willReturn($isRename);
        $file->isDeletion()->willReturn($isDelete);
        return $file->reveal();
    }

    function it_will_list_all_diffed_files(Repository $repository, Filesystem $filesystem, Diff $diff, WorkingCopy $workingCopy)
    {
        $changedFile = $this->mockFile('file1.txt');
        $movedFile = $this->mockFile('file2.txt', true);
        $deletedFile = $this->mockFile('file3.txt', false, true);

        $filesystem->exists('file1.txt')->willReturn(true);
        $filesystem->exists('file2.txt')->willReturn(true);
        $filesystem->exists('file3.txt')->willReturn(false);

        $repository->getWorkingCopy()->willReturn($workingCopy);
        $workingCopy->getDiffStaged()->willReturn($diff);
        $diff->getFiles()->willReturn([$changedFile, $movedFile, $deletedFile]);

        $result = $this->locateFromGitRepository();
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result[0]->getPathname()->shouldBe('file1.txt');
        $result[1]->getPathname()->shouldBe('file2.txt');
        $result->getIterator()->count()->shouldBe(2);
    }

    function it_will_not_list_non_existing_files(Repository $repository, Filesystem $filesystem, Diff $diff, WorkingCopy $workingCopy)
    {
        $changedFile = $this->mockFile('file1.txt');
        $filesystem->exists('file1.txt')->willReturn(false);

        $repository->getWorkingCopy()->willReturn($workingCopy);
        $workingCopy->getDiffStaged()->willReturn($diff);
        $diff->getFiles()->willReturn([$changedFile]);

        $result = $this->locateFromGitRepository();
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->getIterator()->count()->shouldBe(0);
    }

    function it_will_list_all_diffed_files_from_raw_diff_input(Filesystem $filesystem)
    {
        $rawDiff = 'diff --git a/file.txt b/file.txt
new file mode 100644
index 0000000000000000000000000000000000000000..9766475a4185a151dc9d56d614ffb9aaea3bfd42
--- /dev/null
+++ b/file.txt
@@ -0,0 +1 @@
+content
';

        $filesystem->exists('file.txt')->willReturn(true);

        $result = $this->locateFromRawDiffInput($rawDiff);
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result[0]->getPathname()->shouldBe('file.txt');
        $result->getIterator()->count()->shouldBe(1);
    }
}
