<?php

namespace spec\GrumPHP\Collection;

use ArrayIterator;
use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Collection\FilesCollection;
use PhpSpec\ObjectBehavior;
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
        $this->beConstructedWith([$file1, $file2]);
    }

    function letgo()
    {
        unlink($this->tempFile);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FilesCollection::class);
    }

    function it_is_an_array_collection()
    {
        $this->shouldHaveType(ArrayCollection::class);
    }

    function it_should_filter_by_name(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getFilename()->willReturn('file.php');
        $file2->getFilename()->willReturn('file.png');

        $result = $this->name('*.php');
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_names(SplFileInfo $file1, SplFileInfo $file2, SplFileInfo $file3)
    {
        $this->beConstructedWith([$file1, $file2, $file3]);

        $file1->getFilename()->willReturn('file.json');
        $file2->getFilename()->willReturn('file.php');
        $file3->getFilename()->willReturn('file.png');

        $result = $this->names(['*.json', '*.php']);
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->count()->shouldBe(2);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
        $files[1]->shouldBe($file2);
    }

    function it_should_filter_by_not_name(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getFilename()->willReturn('file.php');
        $file2->getFilename()->willReturn('file.png');

        $result = $this->notName('*.png');
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_path(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getRelativePathname()->willReturn('path1/file.php');
        $file2->getRelativePathname()->willReturn('path2/file.png');

        $result = $this->path('path1');
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_paths(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getRelativePathname()->willReturn('path1/file.php');
        $file2->getRelativePathname()->willReturn('path2/file.png');

        $result = $this->paths(['path1', 'path2']);
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->count()->shouldBe(2);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
        $files[1]->shouldBe($file2);
    }

    function it_should_filter_by_not_path(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getRelativePathname()->willReturn('path1/file.php');
        $file2->getRelativePathname()->willReturn('path2/file.png');

        $result = $this->notPath('path2');
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_not_paths(SplFileInfo $file1, SplFileInfo $file2, SplFileInfo $file3)
    {
        $this->beConstructedWith([$file1, $file2, $file3]);

        $file1->getRelativePathname()->willReturn('path1/file.php');
        $file2->getRelativePathname()->willReturn('path2/file.php');
        $file3->getRelativePathname()->willReturn('path3/file.png');

        $result = $this->notPaths(['path2', 'path3']);
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_empty_not_paths(SplFileInfo $file1, SplFileInfo $file2, SplFileInfo $file3)
    {
        $this->beConstructedWith([$file1, $file2, $file3]);

        $file1->getRelativePathname()->willReturn('path1/file.php');
        $file2->getRelativePathname()->willReturn('path2/file.php');
        $file3->getRelativePathname()->willReturn('path3/file.png');

        $result = $this->notPaths([]);
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->count()->shouldBe(3);

        $files = $result->toArray();
        $files[0]->shouldBe($file1);
        $files[1]->shouldBe($file2);
        $files[2]->shouldBe($file3);
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
        $result->shouldBeAnInstanceOf(FilesCollection::class);
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
        $file1->getPathname()->willReturn($this->tempFile);
        $file2->getPathname()->willReturn($this->tempFile);
        $file1->getMTime()->willReturn(strtotime('-4 hours'));
        $file2->getMTime()->willReturn(strtotime('-5 days'));

        $result = $this->date('since yesterday');
        $result->shouldBeAnInstanceOf(FilesCollection::class);
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
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_a_list_of_files(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getPathname()->willReturn('path1/file.php');
        $file2->getPathname()->willReturn('path2/file.php');

        $iterator = new ArrayIterator([$file1->getWrappedObject()]);
        $result = $this->filterByFileList($iterator);
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_filter_by_extension(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getFilename()->willReturn('file.php');
        $file2->getFilename()->willReturn('file.jpg');

        $result = $this->extensions(['php', 'js']);
        $result->count()->shouldBe(1);
        $files = $result->toArray();
        $files[0]->shouldBe($file1);
    }

    function it_should_return_an_empty_list_when_filtering_by_no_extension(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->getFilename()->willReturn('file.php');
        $file2->getFilename()->willReturn('file.jpg');

        $result = $this->extensions([]);
        $result->count()->shouldBe(0);
    }

    function it_should_combine_two_collections_with_ensured_files()
    {
        $file1 = new \SplFileInfo('path1/file1.php');
        $file2 = new \SplFileInfo('path1/file2.php');
        $file3 = new \SplFileInfo('path1/file3.php');

        $this->beConstructedWith([$file2, $file3]);
        $ensureFiles = new FilesCollection([$file1, $file2]);

        $result = $this->ensureFiles($ensureFiles);
        $result->shouldIterateAs([$file2, $file3, $file1]);
        $result->shouldHaveCount(3);
    }

    function it_should_ignore_symlinks(SplFileInfo $file1, SplFileInfo $file2)
    {
        $file1->isLink()->willReturn(true);
        $file2->isLink()->willReturn(false);

        $result = $this->ignoreSymlinks();
        $result->count()->shouldBe(1);
        $result->contains($file1)->shouldBe(false);
        $result->contains($file2)->shouldBe(true);
    }
}
