<?php

namespace spec\GrumPHP\Locator;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use Gitonomy\Git\WorkingCopy;
use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Locator\ChangedFiles;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

/**
 * Class ChangedFilesSpec
 */
class ChangedFilesSpec extends ObjectBehavior
{
    function let(Repository $repository)
    {
        $this->beConstructedWith($repository);
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

    function it_will_list_all_diffed_files(Repository $repository, Diff $diff, WorkingCopy $workingCopy)
    {
        $changedFile = $this->mockFile('file1.txt');
        $movedFile = $this->mockFile('file2.txt', true);
        $deletedFile = $this->mockFile('file3.txt', false, true);

        $repository->getWorkingCopy()->willReturn($workingCopy);
        $workingCopy->getDiffStaged()->willReturn($diff);
        $diff->getFiles()->willReturn([$changedFile, $movedFile, $deletedFile]);

        $result = $this->locateFromGitRepository();
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result[0]->getPathname()->shouldBe('file1.txt');
        $result[1]->getPathname()->shouldBe('file2.txt');
        $result->getIterator()->count()->shouldBe(2);
    }

    function it_will_list_all_diffed_files_from_raw_diff_input()
    {
        $rawDiff = 'diff --git a/file.txt b/file.txt
new file mode 100644
index 0000000000000000000000000000000000000000..9766475a4185a151dc9d56d614ffb9aaea3bfd42
--- /dev/null
+++ b/file.txt
@@ -0,0 +1 @@
+content
';

        $result = $this->locateFromRawDiffInput($rawDiff);
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result[0]->getPathname()->shouldBe('file.txt');
        $result->getIterator()->count()->shouldBe(1);
    }
}
