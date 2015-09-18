<?php

namespace spec\GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GitPreCommitContextSpec extends ObjectBehavior
{
    function let(FilesCollection $files)
    {
        $this->beConstructedWith($files);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\Context\GitPreCommitContext');
    }

    function it_should_be_a_task_context()
    {
        $this->shouldImplement('GrumPHP\Task\Context\ContextInterface');
    }

    function it_should_have_files(FilesCollection $files)
    {
        $this->getFiles()->shouldBe($files);
    }
}
