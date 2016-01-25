<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
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
        $options->getDefinedOptions()->shouldContain('run_always');
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_does_not_do_anything_if_there_are_no_files_and_run_always_is_false(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        ContextInterface $context
    ) {
        $grumPHP->getTaskConfiguration('securitychecker')->willReturn(array('run_always' => false));

        $processBuilder->createArgumentsForCommand('security-checker')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();

        $context->getFiles()->willReturn(new FilesCollection());
        $this->run($context)->shouldBeNull();
    }

    function it_runs_the_suite_if_run_always_is_true(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $grumPHP->getTaskConfiguration('securitychecker')->willReturn(array('run_always' => true));

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('security-checker')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection());
        $this->run($context);
    }

    function it_runs_the_suite_when_composer_has_changed_and_run_always_is_false(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $grumPHP->getTaskConfiguration('securitychecker')->willReturn(array('run_always' => false));

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('security-checker')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('composer.lock', '.', 'composer.lock')
        )));

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

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);
        $process->getOutput()->shouldBeCalled();

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('composer.lock', '.', 'composer.lock')
        )));

        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($context);
    }
}
