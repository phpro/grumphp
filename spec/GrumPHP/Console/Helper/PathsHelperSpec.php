<?php

namespace spec\GrumPHP\Console\Helper;

use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\Filesystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PathsHelperSpec extends ObjectBehavior
{
    function let(GrumPHP $config, Filesystem $fileSystem)
    {
        $this->beConstructedWith($config, $fileSystem, '/grumphp.yml');
    }
    
    function it_is_a_console_helper()
    {
        $this->shouldHaveType('Symfony\Component\Console\Helper\Helper');
    }

    function it_throws_exception_during_get_relative_path_when_file_is_not_found()
    {
        $path = '/non/existent/path/config.yml';
        $this->shouldThrow('GrumPHP\Exception\FileNotFoundException')->duringGetRelativePath($path);
    }

    function it_knows_the_default_configuration_file()
    {
        $this->getDefaultConfigPath()->shouldBe('/grumphp.yml');
    }
}
