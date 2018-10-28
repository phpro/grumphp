<?php

namespace spec\GrumPHP\Console\Helper;

use Composer\Config;
use GrumPHP\Console\Helper\ComposerHelper;
use GrumPHP\Util\ComposerFile;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Helper\Helper;

class ComposerHelperSpec extends ObjectBehavior
{
    function let(ComposerFile $composerFile)
    {
        $this->beConstructedWith($composerFile);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComposerHelper::class);
    }

    function it_is_a_console_helper()
    {
        $this->shouldHaveType(Helper::class);
    }

    function it_has_a_composer_file(ComposerFile $composerFile)
    {
        $this->getComposerFile()->shouldBe($composerFile);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldBe(ComposerHelper::HELPER_NAME);
    }
}
