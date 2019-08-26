<?php

namespace spec\GrumPHP\Locator;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Git\GitRepository;
use GrumPHP\Locator\RegisteredFiles;
use GrumPHP\Util\Paths;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegisteredFilesSpec extends ObjectBehavior
{
    function let(GitRepository $repository, Paths $paths)
    {
        $this->beConstructedWith($repository, $paths);
        $paths->makePathRelativeToProjectDir(Argument::type('string'))->will(
            function (array $arguments): string {
                return $arguments[0];
            }
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RegisteredFiles::class);
    }

    function it_will_list_all_diffed_files(GitRepository $repository, Paths $paths)
    {
        $paths->getProjectDir()->willReturn($projectDir = '/path/to/project');
        $files = ['file1.txt', 'file2.txt'];
        $repository->run('ls-files', [$projectDir])->willReturn(implode(PHP_EOL, $files));

        $result = $this->locate();
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result[0]->getPathname()->shouldBe('file1.txt');
        $result[1]->getPathname()->shouldBe('file2.txt');
        $result->getIterator()->count()->shouldBe(2);
    }
}
