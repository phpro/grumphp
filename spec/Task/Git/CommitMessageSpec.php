<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Git\CommitMessage;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitMessageSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP)
    {
        $this->beConstructedWith($grumPHP);
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_commit_message');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('allow_empty_message');
        $options->getDefinedOptions()->shouldContain('enforce_capitalized_subject');
        $options->getDefinedOptions()->shouldContain('enforce_no_subject_trailing_period');
        $options->getDefinedOptions()->shouldContain('max_body_width');
        $options->getDefinedOptions()->shouldContain('max_subject_width');
        $options->getDefinedOptions()->shouldContain('enforce_single_lined_subject');
        $options->getDefinedOptions()->shouldContain('case_insensitive');
        $options->getDefinedOptions()->shouldContain('multiline');
        $options->getDefinedOptions()->shouldContain('matchers');
        $options->getDefinedOptions()->shouldContain('additional_modifiers');
        $options->getDefinedOptions()->shouldContain('enforce_no_subject_punctuations');
        $options->getDefinedOptions()->shouldContain('type_scope_conventions');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CommitMessage::class);
    }

    function it_is_a_grumphp_task()
    {
        $this->shouldImplement(TaskInterface::class);
    }

    function it_should_run_in_git_commit_msg_context(GitCommitMsgContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_runs_the_suite(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
            'matchers' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST']
        ]);

        $context->getCommitMessage()->willReturn('test');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_exception_if_the_process_fails(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
            'matchers' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST']
        ]);

        $context->getCommitMessage()->willReturn('invalid');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_runs_with_additional_modifiers(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
            'matchers' => ['/.*ümlaut/'],
            'additional_modifiers' => 'u',
        ]);

        $context->getCommitMessage()->willReturn('message containing ümlaut');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_commit_message_is_empty(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => true,
            'enforce_single_lined_subject' => true,
        ]);

        $context->getCommitMessage()->willReturn('');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_subject_starts_with_a_capital_letter(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $commitMessage = <<<'MSG'
Initial commit

Mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_subject_starts_with_a_utf8_capital_letter(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $commitMessage = <<<'MSG'
Årsgång

Mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_subject_starts_with_punctuation_and_a_capital_letter(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $commitMessage = <<<'MSG'
"Initial" commit

Mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_subject_starts_with_a_lowercase_letter(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

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

    function it_should_fail_when_subject_starts_with_a_utf8_lowercase_letter(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

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

    function it_should_fail_when_subject_starts_with_punctuation_and_a_lowercase_letter(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

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

    function it_should_pass_when_subject_starts_with_special_fixup_prefix(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $commitMessage = <<<'MSG'
fixup! commit

This was created by running git commit --fixup=...
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_subject_starts_with_special_squash_prefix(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $commitMessage = <<<'MSG'
squash! commit

This was created by running git commit --squash=...
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_first_line_of_commit_message_is_an_empty_line(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => true,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $commitMessage = <<<'MSG'

There was no first line

This is a mistake.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_commit_message_is_empty(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $context->getCommitMessage()->willReturn('');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_fail_when_commit_message_contains_only_whitespace(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $context->getCommitMessage()->willReturn(' ');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_pass_when_commit_message_starts_with_a_comment(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $commitMessage = <<<'MSG'
# Starts with a comment

Another reasonable line.
MSG;
        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_commit_message_is_not_empty(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => false,
        ]);

        $context->getCommitMessage()->willReturn('when commit message is not empty');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_subject_is_separated_from_body_by_a_blank_line(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => true,
        ]);

        $commitMessage = <<<'MSG'
Initial commit

Mostly cats so far.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_subject_is_not_kept_to_one_line(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => true,
        ]);

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

    function it_should_pass_when_a_line_in_the_message_is_commented_but_longer_than_72_characters(GitCommitMsgContext $context)
    {
        $commitMessage = <<<'MSG'
Some summary

# This line is longer than 72 characters which is clearly be seen by count.
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
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

    function it_should_fail_when_subject_contains_a_trailing_period(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => true,
            'enforce_single_lined_subject' => false,
        ]);

        $context->getCommitMessage()->willReturn('This subject has a period.');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_pass_when_subject_does_not_contain_a_trailing_period(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => true,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => true,
            'enforce_single_lined_subject' => false,
        ]);

        $context->getCommitMessage()->willReturn('This subject has no period');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_pass_when_enforce_type_scope_conventions_is_false(
        GrumPHP $grumPHP,
        GitCommitMsgContext $context
    ) {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => true,
            'type_scope_conventions' => [],
        ]);

        $commitMessage = <<<'MSG'
this subject does not follow the type scope conventions

pass because we set enforce_type_scope_conventions to false

And footer #12
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_type_scope_conventions_does_not_follow_conventions(
        GrumPHP $grumPHP,
        GitCommitMsgContext $context
    ) {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => true,
            'type_scope_conventions' => [
                'types' => []
            ],
        ]);

        $commitMessage = <<<'MSG'
this subject does not follow the type scope conventions

The body ...

And footer #12
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_fail_when_type_scope_conventions_does_not_use_an_available_type(
        GrumPHP $grumPHP,
        GitCommitMsgContext $context
    ) {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => true,
            'type_scope_conventions' => [
                'types' => ['fix']
            ],
        ]);

        $commitMessage = <<<'MSG'
docs: this type is not in the available types array

The body ...

And footer #12
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_pass_when_type_scope_conventions_does_use_an_available_type(
        GrumPHP $grumPHP,
        GitCommitMsgContext $context
    ) {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => true,
            'type_scope_conventions' => [
                'types' => ['fix']
            ],
        ]);

        $commitMessage = <<<'MSG'
fix: this type is in the available types array

The body ...

And footer #12
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_when_type_scope_conventions_does_not_use_an_available_scope(
        GrumPHP $grumPHP,
        GitCommitMsgContext $context
    ) {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => true,
            'type_scope_conventions' => [
                'scopes' => ['user']
            ],
        ]);

        $commitMessage = <<<'MSG'
fix(index): this scope is not in the available scopes array

The body ...

And footer #12
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_should_pass_when_type_scope_conventions_does_use_an_available_scope(
        GrumPHP $grumPHP,
        GitCommitMsgContext $context
    ) {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => true,
            'type_scope_conventions' => [
                'scopes' => ['user']
            ],
        ]);

        $commitMessage = <<<'MSG'
fix(user): this scope is in the available scopes array

The body ...

And footer #12
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_should_fail_if_subject_contains_punctuations(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'allow_empty_message' => false,
            'enforce_capitalized_subject' => false,
            'enforce_no_subject_trailing_period' => false,
            'enforce_single_lined_subject' => true,
            'enforce_no_subject_punctuations' => true,
        ]);

        $commitMessage = <<<'MSG'
fix(user): this subject has punctuations!

The body ...

And footer #12 ?
MSG;

        $context->getCommitMessage()->willReturn($commitMessage);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }
}
