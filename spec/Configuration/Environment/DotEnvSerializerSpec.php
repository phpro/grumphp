<?php

declare(strict_types=1);

namespace spec\GrumPHP\Configuration\Environment;

use PhpSpec\ObjectBehavior;

class DotEnvSerializerSpec extends ObjectBehavior
{
    public function it_can_serialize_bash_vars(): void
    {
        $vars = [
            'VAR1' => 'CONTENT',
            'VAR2' => '"multi space comment"',
            'VAR3' => '$(pwd)',
        ];

        $expected = [
            'export VAR1=CONTENT',
            'export VAR2="multi space comment"',
            'export VAR3=$(pwd)',
        ];

        $this->serialize($vars)->shouldBe(implode("\n", $expected));
    }
}
