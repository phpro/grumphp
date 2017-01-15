<?php

namespace spec\GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\FileNotFoundException;
use GrumPHP\Locator\ExternalCommand;
use GrumPHP\Util\Filesystem;
use SplFileInfo;
use Symfony\Component\Console\Helper\Helper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PathsHelperSpec extends ObjectBehavior
{
    function let(GrumPHP $config, Filesystem $filesystem, ExternalCommand $externalCommandLocator)
    {
        $this->beConstructedWith($config, $filesystem, $externalCommandLocator, '/grumphp.yml');
    }

    function it_is_a_console_helper()
    {
        $this->shouldHaveType(Helper::class);
    }

    function it_throws_exception_during_get_relative_path_when_file_is_not_found()
    {
        $path = '/non/existent/path/config.yml';
        $this->shouldThrow(FileNotFoundException::class)->duringGetRelativePath($path);
    }

    function it_knows_the_default_configuration_file()
    {
        $this->getDefaultConfigPath()->shouldBe('/grumphp.yml');
    }

    function it_does_not_load_ascii_art_when_null_is_configured(GrumPHP $config)
    {
        $config->getAsciiContentPath('resource')->willReturn(null);
        $this->getAsciiContent('resource')->shouldBe('');
    }

    function it_can_load_ascii_art_from_a_user_defined_resource(GrumPHP $config, Filesystem $filesystem)
    {
        $config->getAsciiContentPath('resource')->willReturn($fileName = 'somefile.txt');
        $filesystem->exists($fileName)->willReturn(true);
        $filesystem->readFromFileInfo(Argument::that(function (SplFileInfo $file) use ($fileName) {
            return $file->getPathname() === $fileName;
        }))->willReturn($content = 'ascii');

        $this->getAsciiContent('resource')->shouldBe($content);
    }

    function it_can_load_embedded_ascii_art_when_the_user_defned_resource_does_not_exist(GrumPHP $config, Filesystem $filesystem)
    {
        $config->getAsciiContentPath('resource')->willReturn($fileName = 'embedded.txt');
        $filesystem->exists($fileName)->willReturn(false);
        $filesystem->makePathRelative(Argument::type('string'), Argument::type('string'))->willReturn('./');
        $embeddedFileName = './resources/ascii/embedded.txt';
        $filesystem->exists($embeddedFileName)->willReturn(true);

        $filesystem->readFromFileInfo(Argument::that(function (SplFileInfo $file) use ($embeddedFileName) {
            return $file->getPathname() === $embeddedFileName;
        }))->willReturn($content = 'ascii');

        $this->getAsciiContent('resource')->shouldBe($content);
    }

    function it_embeds_an_error_message_if_the_ascii_art_could_not_be_found(GrumPHP $config, Filesystem $filesystem)
    {
        $config->getAsciiContentPath('resource')->willReturn($fileName = 'invalid.txt');
        $filesystem->exists(Argument::type('string'))->willReturn(false);
        $filesystem->makePathRelative(Argument::type('string'), Argument::type('string'))->willReturn('./');

        $this->getAsciiContent('resource')->shouldBe('ASCII file invalid.txt could not be found.');
    }
}
