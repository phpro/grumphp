<?php

namespace spec\GrumPHP\Locator;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Locator\ListedFiles;
use GrumPHP\Util\Paths;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ListedFilesSpec extends ObjectBehavior
{
    function let(Paths $paths)
    {
        $this->beConstructedWith($paths);
        $paths->makePathRelativeToProjectDir(Argument::type('string'))->will(
            function (array $arguments): string {
                return $arguments[0];
            }
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ListedFiles::class);
    }

    function it_will_list_all_listed_files()
    {
        $files = ['file1.txt', 'file2.txt'];
        $result = $this->locate(implode(PHP_EOL, $files));
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result[0]->getPathname()->shouldBe('file1.txt');
        $result[1]->getPathname()->shouldBe('file2.txt');
        $result->getIterator()->count()->shouldBe(2);
    }
}
