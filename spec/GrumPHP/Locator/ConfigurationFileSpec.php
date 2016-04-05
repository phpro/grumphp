<?php

namespace spec\GrumPHP\Locator;

use Composer\Package\PackageInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class ConfigurationFileSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem)
    {
        $this->beConstructedWith($filesystem);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Locator\ConfigurationFile');
    }

    function it_should_locate_config_file(Filesystem $filesystem)
    {
        $filesystem->exists($this->pathArgument('/composer/grumphp.yml'))->willReturn(true);
        $filesystem->isAbsolutePath($this->pathArgument('/composer/grumphp.yml'))->willReturn(true);

        $this->locate('/composer', null)->shouldMatch($this->pathRegex('/composer/grumphp.yml'));
    }

    function it_should_fall_back_on_dist_file(Filesystem $filesystem)
    {
        $filesystem->exists($this->pathArgument('/composer/grumphp.yml'))->willReturn(false);
        $filesystem->exists($this->pathArgument('/composer/grumphp.yml.dist'))->willReturn(true);
        $filesystem->isAbsolutePath($this->pathArgument('/composer/grumphp.yml.dist'))->willReturn(true);

        $this->locate('/composer', null)->shouldMatch($this->pathRegex('/composer/grumphp.yml.dist'));
    }

    function it_should_use_the_config_file_configured_in_the_composer_file(Filesystem $filesystem, PackageInterface $package)
    {
        $package->getExtra()->willReturn(array(
            'grumphp' => array(
                'config-default-path' => '/composer/exotic/path/grumphp.yml'
            )
        ));

        $filesystem->exists($this->pathArgument('/composer/grumphp.yml'))->willReturn(true);
        $filesystem->exists('/composer/exotic/path/grumphp.yml')->willReturn(true);
        $filesystem->isAbsolutePath('/composer/exotic/path/grumphp.yml')->willReturn(true);

        $this->locate('/composer', $package)->shouldBe('/composer/exotic/path/grumphp.yml');
    }

    function it_should_use_the_config_file_configured_in_the_composer_file_and_fall_back_on_dist(Filesystem $filesystem, PackageInterface $package)
    {
        $package->getExtra()->willReturn(array(
            'grumphp' => array(
                'config-default-path' => '/composer/exotic/path/grumphp.yml'
            )
        ));

        $filesystem->exists($this->pathArgument('/composer/grumphp.yml'))->willReturn(true);
        $filesystem->exists('/composer/exotic/path/grumphp.yml')->willReturn(false);
        $filesystem->exists('/composer/exotic/path/grumphp.yml.dist')->willReturn(true);
        $filesystem->isAbsolutePath('/composer/exotic/path/grumphp.yml.dist')->willReturn(true);

        $this->locate('/composer', $package)->shouldBe('/composer/exotic/path/grumphp.yml.dist');
    }

    function it_should_always_return_absolute_paths(Filesystem $filesystem, PackageInterface $package)
    {
        $package->getExtra()->willReturn(array(
            'grumphp' => array(
                'config-default-path' => 'exotic/path/grumphp.yml'
            )
        ));

        $filesystem->exists($this->pathArgument('/composer/grumphp.yml'))->willReturn(true);
        $filesystem->exists($this->pathArgument('exotic/path/grumphp.yml'))->willReturn(true);
        $filesystem->isAbsolutePath($this->pathArgument('exotic/path/grumphp.yml'))->willReturn(false);

        $this->locate('/composer', $package)->shouldMatch($this->pathRegex('/composer/exotic/path/grumphp.yml'));
    }

    function it_should_locate_config_file_on_empty_composer_configuration(Filesystem $filesystem, PackageInterface $package)
    {
        $package->getExtra()->willReturn(array());

        $filesystem->exists($this->pathArgument('/composer/grumphp.yml'))->willReturn(true);
        $filesystem->isAbsolutePath($this->pathArgument('/composer/grumphp.yml'))->willReturn(true);

        $this->locate('/composer', $package)->shouldMatch($this->pathRegex('/composer/grumphp.yml'));
    }

    private function pathRegex($expected)
    {
        return '#^' . str_replace(array('.', '/'), array('\.', '[\\\/]{1}'), $expected) . '$#i';
    }

    private function pathArgument($expected)
    {
        $regex = $this->pathRegex($expected);

        return Argument::that(function($path) use ($regex) {
            return preg_match($regex, $path);
        });
    }
}
