<?php

namespace spec\GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Console\Helper\PathsHelper;
use GrumPHP\Exception\FileNotFoundException;
use GrumPHP\Locator\ExternalCommand;
use GrumPHP\Util\Filesystem;
use Symfony\Component\Console\Helper\Helper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class PathsHelperSpec
 */
class PathsHelperSpec extends ObjectBehavior
{
    function let(GrumPHP $config, Filesystem $fileSystem, ExternalCommand $externalCommandLocator)
    {
        $this->beConstructedWith($config, $fileSystem, $externalCommandLocator, '/grumphp.yml');
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
}
