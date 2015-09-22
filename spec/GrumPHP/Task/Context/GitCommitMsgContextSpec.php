<?php

namespace spec\GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GitCommitMsgContextSpec extends ObjectBehavior
{
    /**
     * @var string
     */
    protected $tempFile;

    function let(FilesCollection $files, \SplFileInfo $fileInfo)
    {
        $this->beConstructedWith($files, $fileInfo, 'user', 'user@email.com');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\Context\GitCommitMsgContext');
    }

    function it_should_be_a_task_context()
    {
        $this->shouldImplement('GrumPHP\Task\Context\ContextInterface');
    }

    function it_should_have_files(FilesCollection $files)
    {
        $this->getFiles()->shouldBe($files);
    }

    function it_should_know_the_git_user()
    {
        $this->getUserName()->shouldBe('user');
    }

    function it_should_know_the_git_email()
    {
        $this->getUserEmail()->shouldBe('user@email.com');
    }
}
