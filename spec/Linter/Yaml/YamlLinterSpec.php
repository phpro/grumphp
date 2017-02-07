<?php

namespace spec\GrumPHP\Linter\Yaml;

use GrumPHP\Linter\LinterInterface;
use GrumPHP\Linter\Yaml\YamlLinter;
use GrumPHP\Util\Filesystem;
use PhpSpec\ObjectBehavior;

class YamlLinterSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem)
    {
        $this->beConstructedWith($filesystem);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(YamlLinter::class);
    }

    function it_is_a_linter()
    {
        $this->shouldImplement(LinterInterface::class);
    }
}
