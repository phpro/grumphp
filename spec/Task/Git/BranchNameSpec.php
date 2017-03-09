<?php

namespace spec\GrumPHP\Task\Git;

use Gitonomy\Git\Reference;
use Gitonomy\Git\Repository;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Git\BranchName;
use GrumPHP\Task\TaskInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BranchNameSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, Repository $repository, Reference $reference)
    {
        $this->beConstructedWith($grumPHP);
        $grumPHP->getTaskConfiguration('git_branch_name')->willReturn([
            'matchers' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST']
        ]);
        $reference->getRevision()->willReturn('refs/heads/test');
        $repository->getHead()->willReturn($reference);
        $grumPHP->getGitRepository()->willReturn($repository);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_branch_name');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('case_insensitive');
        $options->getDefinedOptions()->shouldNotContain('multiline');
        $options->getDefinedOptions()->shouldContain('matchers');
        $options->getDefinedOptions()->shouldContain('additional_modifiers');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(BranchName::class);
    }

    function it_is_a_grumphp_task()
    {
        $this->shouldImplement(TaskInterface::class);
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

    function it_throws_exception_if_the_process_fails(RunContext $context, Reference $reference)
    {
        $reference->getRevision()->willReturn('refs/heads/not-good');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_runs_with_additional_modifiers(RunContext $context, GrumPHP $grumPHP, Reference $reference)
    {
        $grumPHP->getTaskConfiguration('git_branch_name')->willReturn([
            'matchers' => ['/good/'],
            'additional_modifiers' => 'u',
        ]);

        $reference->getRevision()->willReturn('refs/heads/good');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }
}
