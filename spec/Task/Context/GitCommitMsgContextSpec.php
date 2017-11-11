<?php

namespace spec\GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use PhpSpec\ObjectBehavior;

class GitCommitMsgContextSpec extends ObjectBehavior
{
    /**
     * @var string
     */
    protected $tempFile;

    public function let(FilesCollection $files)
    {
        $this->beConstructedWith($files, 'message', 'user', 'user@email.com');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(GitCommitMsgContext::class);
    }

    public function it_should_be_a_task_context()
    {
        $this->shouldImplement(ContextInterface::class);
    }

    public function it_should_have_files(FilesCollection $files)
    {
        $this->getFiles()->shouldBe($files);
    }

    public function it_should_know_the_git_user()
    {
        $this->getUserName()->shouldBe('user');
    }

    public function it_should_know_the_git_email()
    {
        $this->getUserEmail()->shouldBe('user@email.com');
    }

    public function it_knows_the_commit_message()
    {
        $this->getCommitMessage()->shouldBe('message');
    }
}
