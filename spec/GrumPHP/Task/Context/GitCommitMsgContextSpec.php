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

    function it_should_know_the_commit_msg(\SplFileInfo $fileInfo)
    {
        $message = 'message';
        $messageSize = strlen($message);

        $stream = new \SplFileObject('php://memory', 'w');
        $stream->fwrite($message);
        $stream->rewind();
        $fileInfo->openFile('r')->shouldBeCalledTimes(1)->willReturn($stream);
        $fileInfo->getSize()->willReturn($messageSize);

        $this->getCommitMessage()->shouldReturn('message');

        // Ask the message again to make sure it is not read twice:
        $this->getCommitMessage()->shouldReturn('message');
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
