<?php

namespace spec\GrumPHP\Locator;

use GitElephant\Repository;
use GitElephant\Status\Status;
use GitElephant\Status\StatusFile;
use GrumPHP\Finder\FinderFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\Finder;

class ChangedFilesSpec extends ObjectBehavior
{
    function let(Repository $repository, FinderFactory $finderFactory, Status $status)
    {
        $this->beConstructedWith($repository, $finderFactory);
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

    function it_excludes_untracked_files(FinderFactory $finderFactory, Status $status, StatusFile $file1, StatusFile $file2)
    {
        $file1->getName()->willReturn('match1');
        $file1->getType()->willReturn(StatusFile::UNTRACKED);
        $file2->getName()->willReturn('match2');
        $file2->getType()->willReturn(StatusFile::MODIFIED);
        $status->all()->willReturn(array($file1, $file2));

        $finder = Finder::create();
        $finderFactory->create(array('match2'))->willReturn($finder);
        $this->locate()->shouldBe($finder);
    }

    function it_excludes_deleted_files(FinderFactory $finderFactory, Status $status, StatusFile $file1, StatusFile $file2)
    {
        $file1->getName()->willReturn('match1');
        $file1->getType()->willReturn(StatusFile::MODIFIED);
        $file2->getName()->willReturn('match2');
        $file2->getType()->willReturn(StatusFile::DELETED);
        $status->all()->willReturn(array($file1, $file2));

        $finder = Finder::create();
        $finderFactory->create(array('match1'))->willReturn($finder);
        $this->locate()->shouldBe($finder);
    }
}
