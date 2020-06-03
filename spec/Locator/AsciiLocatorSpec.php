<?php

namespace spec\GrumPHP\Locator;

use GrumPHP\Configuration\Model\AsciiConfig;
use GrumPHP\Util\Filesystem;
use GrumPHP\Util\Paths;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Locator\AsciiLocator;

class AsciiLocatorSpec extends ObjectBehavior
{
    public function let(Filesystem $filesystem, Paths $paths): void
    {
        $config = new AsciiConfig(['success' => 'file.txt']);
        $this->beConstructedWith($config, $filesystem, $paths);

    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AsciiLocator::class);
    }

    public function it_returns_empty_string_on_unkown_resource(): void
    {
        $this->locate('unknown')->shouldBe('');
    }

    public function it_returns_content_on_known_file(Filesystem $filesystem): void
    {
        $file = 'file.txt';
        $filesystem->exists($file)->willReturn(true);
        $filesystem->readFromFileInfo(new \SplFileInfo($file))->willReturn($content = 'content');

        $this->locate('success')->shouldBe($content);
    }

    public function it_returns_content_from_internal_storage(Filesystem $filesystem, Paths $paths): void
    {
        $file = 'file.txt';
        $paths->getInternalAsciiPath()->willReturn('asciipath');
        $filesystem->exists($file)->willReturn(false);
        $filesystem->buildPath('asciipath', $file)->willReturn($asciiFile = 'asciipath/file.txt');
        $filesystem->exists($asciiFile)->willReturn(true);
        $filesystem->readFromFileInfo(new \SplFileInfo($asciiFile))->willReturn($content = 'content');

        $this->locate('success')->shouldBe($content);
    }

    public function it_returns_exception_on_file_not_found(Filesystem $filesystem, Paths $paths): void
    {
        $file = 'file.txt';
        $paths->getInternalAsciiPath()->willReturn('asciipath');
        $filesystem->exists($file)->willReturn(false);
        $filesystem->buildPath('asciipath', $file)->willReturn($asciiFile = 'asciipath/file.txt');
        $filesystem->exists($asciiFile)->willReturn(false);

        $this->locate('success')->shouldBe('ASCII file '.$file.' could not be found.');
    }
}
