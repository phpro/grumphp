<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Git\CapitalizedSubject;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CapitalizedSubjectSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP)
    {
        $this->beConstructedWith($grumPHP);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_capitalized_subject');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CapitalizedSubject::class);
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

    function it_should_pass_when_subject_starts_with_a_capital_letter(GitCommitMsgContext $context)
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

    function it_should_pass_when_subject_starts_with_a_utf8_capital_letter(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
Årsgång

Mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_subject_starts_with_punctuation_and_a_capital_letter(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
"Initial" commit

Mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_subject_starts_with_a_lowercase_letter(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
initial commit

I forget about commit message standards and decide to not capitalize my
subject. Still mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_fail_when_subject_starts_with_a_utf8_lowercase_letter(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
årsgång

I forget about commit message standards and decide to not capitalize my
subject. Still mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_fail_when_subject_starts_with_punctuation_and_a_lowercase_letter(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
"initial" commit

I forget about commit message standards and decide to not capitalize my
subject. Still mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_pass_when_subject_starts_with_special_fixup_prefix(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
fixup! commit

This was created by running git commit --fixup=...
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_subject_starts_with_special_squash_prefix(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
squash! commit

This was created by running git commit --squash=...
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_first_line_of_commit_message_is_an_empty_line(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'

There was no first line

This is a mistake.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }
}
