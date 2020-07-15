<?php

namespace spec\GrumPHP\Configuration\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use GrumPHP\Configuration\Model\EnvConfig;

class EnvConfigSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            ['file1.ini'],
            ['var1' => 'content'],
            ['path1']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EnvConfig::class);
    }

    public function it_contains_env_files(): void
    {
        $this->hasFiles()->shouldBe(true);
        $this->getFiles()->shouldBe(['file1.ini']);
    }

    public function it_contains_env_vars(): void
    {
        $this->hasVariables()->shouldBe(true);
        $this->getVariables()->shouldBe(['var1' => 'content']);
    }

    public function it_contains_paths(): void
    {
        $this->hasPaths()->shouldBe(true);
        $this->getPaths()->shouldBe(['path1']);
    }

    public function it_can_be_constructed_from_array(): void
    {
        $this->beConstructedThrough('fromArray', [
            [
                'files' => $files = [],
                'variables' => $variables = [],
                'paths' => $paths = [],
            ]
        ]);

        $this->hasFiles()->shouldBe(false);
        $this->getFiles()->shouldBe($files);
        $this->hasVariables()->shouldBe(false);
        $this->getVariables()->shouldBe($variables);
        $this->hasPaths()->shouldBe(false);
        $this->getPaths()->shouldBe($paths);
    }
}
