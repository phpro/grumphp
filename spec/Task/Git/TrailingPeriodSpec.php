<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Git\TrailingPeriod;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrailingPeriodSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP)
    {
        $this->beConstructedWith($grumPHP);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_trailing_period');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TrailingPeriod::class);
    }

    function it_is_a_grumphp_task()
    {
        $this->shouldImplement(TaskInterface::class);
    }

    function it_should_run_in_git_commit_msg_context(GitCommitMsgContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_pass_when_commit_message_is_empty(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn('');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_subject_contains_a_trailing_period(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn('This subject has a period.');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_pass_when_subject_does_not_contain_a_trailing_period(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn('This subject has no period');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }
}
