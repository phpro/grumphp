<?php

namespace spec\GrumPHP\Locator;

use GitElephant\Repository;
use GitElephant\Status\Status;
use GitElephant\Status\StatusFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangedFilesSpec extends ObjectBehavior
{
    function let(Repository $repository, Status $status)
    {
        $this->beConstructedWith($repository);
        $repository->getStatus()->shouldBeCalled()->willReturn($status);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Locator\ChangedFiles');
    }

    function it_is_a_grumphp_locator()
    {
        $this->shouldHaveType('GrumPHP\Locator\LocatorInterface');
    }

    function it_gets_the_status_from_the_repository(Status $status)
    {
        $this->getStatus()->shouldEqual($status);
    }

    function it_excludes_untracked_files(Status $status, StatusFile $file1, StatusFile $file2)
    {
        $file1->getName()->willReturn('match1');
        $file1->getType()->willReturn(StatusFile::UNTRACKED);
        $file2->getName()->willReturn('match2');
        $file2->getType()->willReturn(StatusFile::MODIFIED);
        $status->all()->willReturn(array($file1, $file2));

        $result = $this->locate();
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result[0]->getPathname()->shouldBe('match2');
        $result->getIterator()->count()->shouldBe(1);

    }

    function it_excludes_deleted_files(Status $status, StatusFile $file1, StatusFile $file2)
    {
        $file1->getName()->willReturn('match1');
        $file1->getType()->willReturn(StatusFile::MODIFIED);
        $file2->getName()->willReturn('match2');
        $file2->getType()->willReturn(StatusFile::DELETED);
        $status->all()->willReturn(array($file1, $file2));

        $result = $this->locate();
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result[0]->getPathname()->shouldBe('match1');
        $result->getIterator()->count()->shouldBe(1);
    }
}
