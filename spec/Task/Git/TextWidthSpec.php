<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Git\TextWidth;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextWidthSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP)
    {
        $this->beConstructedWith($grumPHP);
        $grumPHP->getTaskConfiguration('git_text_width')->willReturn([
            'max_body_width' => 72,
            'max_subject_width' => 60,
        ]);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_text_width');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('max_body_width');
        $options->getDefinedOptions()->shouldContain('max_subject_width');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TextWidth::class);
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

    function it_should_fail_when_subject_is_longer_than_60_characters(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn(str_repeat('A', 61));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
        $result->getMessage()->shouldMatch('/subject/');
    }

    function it_should_pass_when_subject_is_60_characters_or_fewer(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn(str_repeat('A', 60));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_subject_starts_with_special_fixup_and_is_longer_than_60_characters(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn('fixup! '.str_repeat('A', 60));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_subject_starts_with_special_squash_and_is_longer_than_60_characters(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn('squash! '.str_repeat('A', 60));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_the_subject_is_60_characters_followed_by_a_newline(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
This is 60 characters, or 61 if the newline is counted

A reasonable line.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_a_line_in_the_message_is_72_characters_followed_by_a_newline(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
Some summary

This line has 72 characters, but with newline it has 73 characters
That shouldn't be a problem.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_a_line_in_the_message_is_longer_than_72_characters(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
Some summary

This line is longer than 72 characters which is clearly be seen by count.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
        $result->getMessage()->shouldBe('Line 3 of commit message has > 72 characters.');
    }

    function it_should_pass_when_all_lines_in_the_message_are_fewer_than_72_characters(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
Some summary

A reasonable line.

Another reasonable line.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_subject_and_a_line_in_the_message_is_longer_than_the_limits(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
A subject line that is way too long. A subject line that is way too long.

A message line that is way too long. A message line that is way too long.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
        $result->getMessage()->shouldMatch('/keep.*subject <= 60.*\n.*line 3.*> 72.*/im');
    }
}
