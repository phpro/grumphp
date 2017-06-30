<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Git\SingleLineSubject;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SingleLineSubjectSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP)
    {
        $this->beConstructedWith($grumPHP);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_single_line_subject');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SingleLineSubject::class);
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

    function it_should_pass_when_subject_is_separated_from_body_by_a_blank_line(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
Initial commit

Mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_subject_is_not_kept_to_one_line(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
Initial commit where I forget about commit message
standards and decide to hard-wrap my subject

Still mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }
}
