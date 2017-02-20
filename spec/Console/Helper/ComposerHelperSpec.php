<?php

namespace spec\GrumPHP\Console\Helper;

use Composer\Config;
use Composer\Package\RootPackage;
use GrumPHP\Console\Helper\ComposerHelper;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Helper\Helper;

class ComposerHelperSpec extends ObjectBehavior
{
    public function let(Config $config, RootPackage $rootPackage)
    {
        $this->beConstructedWith($config, $rootPackage);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ComposerHelper::class);
    }

    public function it_is_a_console_helper()
    {
        $this->shouldHaveType(Helper::class);
    }

    public function it_knows_if_the_composer_configuration_is_available()
    {
        $this->hasConfiguration()->shouldBe(true);
    }

    public function it_has_composer_configuration(Config $config)
    {
        $this->getConfiguration()->shouldBe($config);
    }

    public function it_knows_if_the_composer_root_package_is_available()
    {
        $this->hasRootPackage()->shouldBe(true);
    }

    public function it_has_composer_root_package(RootPackage $rootPackage)
    {
        $this->getRootPackage()->shouldBe($rootPackage);
    }
}
