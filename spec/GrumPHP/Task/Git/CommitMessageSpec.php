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
            'matchers' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST']
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
        $options->getDefinedOptions()->shouldContain('case_insensitive');
        $options->getDefinedOptions()->shouldContain('multiline');
        $options->getDefinedOptions()->shouldContain('matchers');
        $options->getDefinedOptions()->shouldContain('additional_modifiers');
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

    function it_runs_the_suite(GitCommitMsgContext $context)
    {
        $context->getCommitMessage()->willReturn('test');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_exception_if_the_process_fails(GitCommitMsgContext $context) {
        $context->getCommitMessage()->willReturn('invalid');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_runs_with_additional_modifiers(GrumPHP $grumPHP, GitCommitMsgContext $context)
    {
        $grumPHP->getTaskConfiguration('git_commit_message')->willReturn([
            'matchers' => ['/.*ümlaut/'],
            'additional_modifiers' => 'u',
        ]);

        $context->getCommitMessage()->willReturn('message containing ümlaut');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }
}
