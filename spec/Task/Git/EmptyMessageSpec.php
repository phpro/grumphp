<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Git\EmptyMessage;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmptyMessageSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP)
    {
        $this->beConstructedWith($grumPHP);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_empty_message');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EmptyMessage::class);
    }

    function it_is_a_grumphp_task()
    {
        $this->shouldImplement(TaskInterface::class);
    }

    function it_should_run_in_git_commit_msg_context(GitCommitMsgContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_fail_when_commit_message_is_empty(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn('');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_fail_when_commit_message_contains_only_whitespace(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn(' ');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_pass_when_commit_message_is_not_empty(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn('when commit message is not empty');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }
}
