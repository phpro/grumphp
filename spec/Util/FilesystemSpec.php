<?php

namespace spec\GrumPHP\Util;

use PhpSpec\ObjectBehavior;
use GrumPHP\Util\Filesystem;
use SplFileInfo;
use SplFileObject;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class FilesystemSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Filesystem::class);
    }

    function it_extends_symfony_filessystem()
    {
        $this->shouldHaveType(SymfonyFilesystem::class);
    }

    function it_can_read_file_objects(SplFileInfo $file)
    {
        $content = new SplFileObject('php://memory', 'r+');
        $content->fwrite('content');
        $content->rewind();
        $file->openFile('r')->willReturn($content);

        $this->readFromFileInfo($file)->shouldBe('content');
    }
}
