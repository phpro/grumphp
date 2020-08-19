<?php

namespace spec\GrumPHP\Locator;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Git\GitRepository;
use GrumPHP\Locator\ListedFiles;
use GrumPHP\Locator\RegisteredFiles;
use GrumPHP\Util\Paths;
use PhpSpec\ObjectBehavior;

class RegisteredFilesSpec extends ObjectBehavior
{
    function let(GitRepository $repository, Paths $paths, ListedFiles $listedFiles)
    {
        $this->beConstructedWith($repository, $paths, $listedFiles);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RegisteredFiles::class);
    }

    function it_will_list_all_diffed_files(GitRepository $repository, Paths $paths, ListedFiles $listedFiles)
    {
        $paths->getProjectDir()->willReturn($projectDir = '/path/to/project');
        $files = ['file1.txt', 'file2.txt'];
        $repository->run('ls-files', [$projectDir])->willReturn($fileList = implode(PHP_EOL, $files));
        $listedFiles->locate($fileList)->willReturn($expected = new FilesCollection());

        $result = $this->locate();
        $result->shouldBe($expected);
    }
}
