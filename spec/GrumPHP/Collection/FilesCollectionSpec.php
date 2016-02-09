<?php

namespace spec\GrumPHP\Collection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;

class FilesCollectionSpec extends ObjectBehavior
{
    /**
     * @var string
     */
    protected $tempFile;

    function let(SplFileInfo $file1, SplFileInfo $file2)
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'phpspec');
        $this->beConstructedWith(array($file1, $file2));
    }

    function letgo()
    {
        unlink($this->tempFile);
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
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_not_name(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getFilename()->willReturn('file.php');
        $file2->getFilename()->willReturn('file.png');

        $result = $this->notName('*.png');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_path(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getRelativePathname()->willReturn('path1/file.php');
        $file2->getRelativePathname()->willReturn('path2/file.png');

        $result = $this->path('path1');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_not_path(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getRelativePathname()->willReturn('path1/file.php');
        $file2->getRelativePathname()->willReturn('path2/file.png');

        $result = $this->notPath('path2');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_size(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->isFile()->willReturn(true);
        $file2->isFile()->willReturn(true);
        $file1->getRealPath()->willReturn($this->tempFile);
        $file2->getRealPath()->willReturn($this->tempFile);
        $file1->getSize()->willReturn(8 * 1024);
        $file2->getSize()->willReturn(16 * 1024);

        $result = $this->size('>= 4K')->size('<= 10K');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_date(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->isFile()->willReturn(true);
        $file2->isFile()->willReturn(true);
        $file1->getRealPath()->willReturn($this->tempFile);
        $file2->getRealPath()->willReturn($this->tempFile);
        $file1->getMTime()->willReturn(strtotime('-4 hours'));
        $file2->getMTime()->willReturn(strtotime('-5 days'));

        $result = $this->date('since yesterday');
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_callback(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getRelativePathname()->willReturn('path1/file.php');
        $file2->getRelativePathname()->willReturn('path2/file.png');

        $result = $this->filter(function (SplFileInfo $file) {
            return $file->getRelativePathname() === 'path1/file.php';
        });
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_a_list_of_files(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getPathname()->willReturn('path1/file.php');
        $file2->getPathname()->willReturn('path2/file.php');

        $iterator = new \ArrayIterator(array($file1->getWrappedObject()));
        $result = $this->filterByFileList($iterator);
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_extension(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getFilename()->willReturn('file.php');
        $file2->getFilename()->willReturn('file.jpg');

        $result = $this->extensions(array('php', 'js'));
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_return_an_empty_list_when_filtering_by_no_extension(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getFilename()->willReturn('file.php');
        $file2->getFilename()->willReturn('file.jpg');

        $result = $this->extensions(array());
        $result->count()->shouldBe(0);
    }
}
