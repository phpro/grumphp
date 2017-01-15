<?php

namespace spec\GrumPHP\Linter\Json;

use GrumPHP\Linter\Json\JsonLinter;
use GrumPHP\Linter\LinterInterface;
use GrumPHP\Util\Filesystem;
use PhpSpec\ObjectBehavior;
use Seld\JsonLint\JsonParser;

class JsonLinterSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem, JsonParser $jsonParser)
    {
        $this->beConstructedWith($filesystem, $jsonParser);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JsonLinter::class);
    }

    function it_is_a_linter()
    {
        $this->shouldImplement(LinterInterface::class);
    }
}
