<?php

namespace spec\GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use PhpSpec\ObjectBehavior;

class GitPreCommitContextSpec extends ObjectBehavior
{
    public function let(FilesCollection $files)
    {
        $this->beConstructedWith($files);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GitPreCommitContext::class);
    }

    public function it_should_be_a_task_context()
    {
        $this->shouldImplement(ContextInterface::class);
    }

    public function it_should_have_files(FilesCollection $files)
    {
        $this->getFiles()->shouldBe($files);
    }
}
