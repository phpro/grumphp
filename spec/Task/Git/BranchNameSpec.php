<?php

namespace spec\GrumPHP\Task\Git;

use Gitonomy\Git\Exception\ProcessException;
use Gitonomy\Git\Repository;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Git\BranchName;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BranchNameSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, Repository $repository)
    {
        $this->beConstructedWith($grumPHP, $repository);
        $grumPHP->getTaskConfiguration('git_branch_name')->willReturn([
            'whitelist' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST'],
            'blacklist' => ['develop', 'master'],
            'additional_modifiers' => 'i',
        ]);
        $repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('test');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_branch_name');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('whitelist');
        $options->getDefinedOptions()->shouldContain('blacklist');
        $options->getDefinedOptions()->shouldContain('allow_detached_head');
        $options->getDefinedOptions()->shouldContain('additional_modifiers');
        $options->getDefinedOptions()->shouldContain('allow_detached_head');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BranchName::class);
    }

    function it_is_a_grumphp_task()
    {
        $this->shouldImplement(TaskInterface::class);
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_runs_the_suite(RunContext $context)
    {
        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_exception_if_the_process_fails(RunContext $context, Repository $repository)
    {
        $repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('not-good');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_runs_with_additional_modifiers(RunContext $context, GrumPHP $grumPHP, Repository $repository)
    {
        $grumPHP->getTaskConfiguration('git_branch_name')->willReturn([
            'whitelist' => ['/^ümlaut/'],
            'additional_modifiers' => 'u',
        ]);

        $repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('ümlaut-branch-name');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_runs_with_detached_head_setting(RunContext $context, GrumPHP $grumPHP, Repository $repository)
    {
        $repository->run('symbolic-ref', ['HEAD', '--short'])->willThrow(ProcessException::class);

        $grumPHP->getTaskConfiguration('git_branch_name')->willReturn([
          'allow_detached_head' => true,
        ]);

        $result = $this->run($context);
        $result->isPassed()->shouldBe(true);

        $grumPHP->getTaskConfiguration('git_branch_name')->willReturn([
          'allow_detached_head' => false,
        ]);

        $result = $this->run($context);
        $result->isPassed()->shouldBe(false);
    }

    function it_runs_with_blacklisted_items(RunContext $context, GrumPHP $grumPHP, Repository $repository)
    {
        $grumPHP->getTaskConfiguration('git_branch_name')->willReturn([
            'blacklist' => [
                'master',
                'develop',
            ]
        ]);
        $repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('master');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }
}
