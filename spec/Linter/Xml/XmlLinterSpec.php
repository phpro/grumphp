<?php

namespace spec\GrumPHP\Linter\Xml;

use GrumPHP\Linter\LinterInterface;
use GrumPHP\Linter\Xml\XmlLinter;
use PhpSpec\ObjectBehavior;

class XmlLinterSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(XmlLinter::class);
    }

    public function it_is_a_linter()
    {
        $this->shouldImplement(LinterInterface::class);
    }
}
