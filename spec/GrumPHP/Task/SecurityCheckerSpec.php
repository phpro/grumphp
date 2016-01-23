<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

class SecurityCheckerSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder)
    {
        $grumPHP->getTaskConfiguration('securitychecker')->willReturn(array());
        $this->beConstructedWith($grumPHP, $processBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\SecurityChecker');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('securitychecker');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver');
        $options->getDefinedOptions()->shouldContain('lockfile');
        $options->getDefinedOptions()->shouldContain('format');
        $options->getDefinedOptions()->shouldContain('end_point');
        $options->getDefinedOptions()->shouldContain('timeout');
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_runs_the_suite(ProcessBuilder $processBuilder, Process $process, ContextInterface $context)
    {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('security-checker')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $this->run($context);
    }

    function it_throws_an_exception_if_the_process_fails(
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('security-checker')->willReturn($arguments);
        $processBuilder->buildProcess(Argument::type('GrumPHP\Collection\ProcessArgumentsCollection'))->willReturn($process);

        $process->getOutput()->shouldBeCalled();
        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);

        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($context);
    }
}
