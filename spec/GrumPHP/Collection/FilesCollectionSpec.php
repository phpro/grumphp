<?php

namespace spec\GrumPHP\Collection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SplFileInfo;

class FilesCollectionSpec extends ObjectBehavior
{
    public function let(SplFileInfo $file1, SplFileInfo $file2)
    {
        $this->beConstructedWith(array($file1, $file2));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Collection\FilesCollection');
    }

    function it_is_an_array_collection()
    {
        $this->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
    }

    function it_should_filter_by_name(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getFilename()->willReturn('file.php');
        $file2->getFilename()->willReturn('file.png');

        $result = $this->name('*.php');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->toArray()[0]->shouldBe($file1);
        $result->count()->shouldBe(1);
    }

    function it_should_filter_by_not_name(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getFilename()->willReturn('file.php');
        $file2->getFilename()->willReturn('file.png');

        $result = $this->notName('*.png');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->toArray()[0]->shouldBe($file1);
        $result->count()->shouldBe(1);
    }

    function it_should_filter_by_path(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getPath()->willReturn('path1/file.php');
        $file2->getPath()->willReturn('path2/file.png');

        $result = $this->path('path1/*');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->toArray()[0]->shouldBe($file1);
        $result->count()->shouldBe(1);
    }

    function it_should_filter_by_not_path(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getPath()->willReturn('path1/file.php');
        $file2->getPath()->willReturn('path2/file.png');

        $result = $this->notPath('path2/*');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->toArray()[0]->shouldBe($file1);
        $result->count()->shouldBe(1);
    }

    function it_should_filter_by_size(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getSize()->willReturn(8 * 1024);
        $file2->getSize()->willReturn(16 * 1024);

        $result = $this->size('>= 4K')->size('<= 10K');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->toArray()[0]->shouldBe($file1);
        $result->count()->shouldBe(1);
    }

    function it_should_filter_by_date(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getMTime()->willReturn(strtotime('-4 hours'));
        $file2->getMTime()->willReturn(strtotime('-5 days'));

        $result = $this->date('since yesterday');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->toArray()[0]->shouldBe($file1);
        $result->count()->shouldBe(1);
        // Test
    }

}
