<?php

namespace spec\GrumPHP\Locator;

use Gitonomy\Git\Diff\Diff;
use Gitonomy\Git\Diff\File;
use Gitonomy\Git\WorkingCopy;
use Gitonomy\Git\Repository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

class ChangedFilesSpec extends ObjectBehavior
{
    function let(Repository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Locator\ChangedFiles');
    }

    protected function mockFile($name, $isRename = false, $isDelete = false)
    {
        $prophet = new Prophet();
        $file = $prophet->prophesize('Gitonomy\Git\Diff\File');
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
        $diff->getFiles()->willReturn(array($changedFile, $movedFile, $deletedFile));

        $result = $this->locate();
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result[0]->getPathname()->shouldBe('file1.txt');
        $result[1]->getPathname()->shouldBe('file2.txt');
        $result->getIterator()->count()->shouldBe(2);
    }
}
