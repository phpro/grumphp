<?php

namespace spec\GrumPHP\Task\Context;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use PhpSpec\ObjectBehavior;

class GitCommitMsgContextSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('message', 'user', 'user@email.com');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GitCommitMsgContext::class);
    }

    function it_should_be_a_task_context()
    {
        $this->shouldImplement(ContextInterface::class);
    }

    function it_should_have_files()
    {
        $this->getFiles()->shouldHaveCount(0);
    }

    function it_should_know_the_git_user()
    {
        $this->getUserName()->shouldBe('user');
    }

    function it_should_know_the_git_email()
    {
        $this->getUserEmail()->shouldBe('user@email.com');
    }

    function it_knows_the_commit_message()
    {
        $this->getCommitMessage()->shouldBe('message');
    }
}
