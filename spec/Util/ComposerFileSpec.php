<?php

namespace spec\GrumPHP\Util;

use GrumPHP\Util\ComposerFile;
use PhpSpec\ObjectBehavior;

class ComposerFileSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([
            'config' => [
                'sort-packages' => true,
            ]
        ]);
        $this->shouldHaveType(ComposerFile::class);
    }

    function it_should_create_itself_from_a_composer_file_location()
    {
        $this->beConstructedThrough('createFrom', ['composer.json']);
        $this->ensureProjectBinDirInSystemPath()->shouldBe(true);
    }

    function it_should_have_a_default_bin_dir()
    {
        $this->beConstructedWith([
            'config' => [
                'sort-packages' => true,
            ]
        ]);

        $this->getBinDir()->shouldBe('vendor/bin');
    }

    function it_should_have_a_custom_bin_dir()
    {
        $this->beConstructedWith([
            'config' => [
                'sort-packages' => true,
                'bin-dir' => 'bin'
            ]
        ]);

        $this->getBinDir()->shouldBe('bin');
    }

    function it_should_have_a_default_config_path()
    {
        $this->beConstructedWith([
            'config' => [
                'sort-packages' => true,
            ]
        ]);

        $this->getConfigDefaultPath()->shouldBe(null);
    }

    function it_should_have_a_custom_config_path()
    {
        $this->beConstructedWith([
            'config' => [
                'sort-packages' => true,
            ],
            'extra' => [
                'grumphp' => [
                    'config-default-path' => 'some/folder'
                ]
            ]
        ]);

        $this->getConfigDefaultPath()->shouldBe('some/folder');
    }
}
