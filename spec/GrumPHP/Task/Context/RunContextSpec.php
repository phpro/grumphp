<?php

namespace spec\GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use PhpSpec\ObjectBehavior;

class RunContextSpec extends ObjectBehavior
{
    function let(FilesCollection $files)
    {
        $this->beConstructedWith($files);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RunContext::class);
    }

    function it_should_be_a_task_context()
    {
        $this->shouldImplement(ContextInterface::class);
    }

    function it_should_have_files(FilesCollection $files)
    {
        $this->getFiles()->shouldBe($files);
    }
}
