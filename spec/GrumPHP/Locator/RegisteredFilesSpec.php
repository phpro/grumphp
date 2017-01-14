<?php

namespace spec\GrumPHP\Locator;

use Gitonomy\Git\Repository;
use GrumPHP\Collection\FilesCollection;
use GrumPHP\Locator\RegisteredFiles;
use PhpSpec\ObjectBehavior;

class RegisteredFilesSpec extends ObjectBehavior
{
    function let(Repository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RegisteredFiles::class);
    }

    function it_will_list_all_diffed_files(Repository $repository)
    {
        $files = ['file1.txt', 'file2.txt'];
        $repository->run('ls-files')->willReturn(implode(PHP_EOL, $files));

        $result = $this->locate();
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result[0]->getPathname()->shouldBe('file1.txt');
        $result[1]->getPathname()->shouldBe('file2.txt');
        $result->getIterator()->count()->shouldBe(2);
    }
}
