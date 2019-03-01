<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\SecurityChecker;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class SecurityCheckerSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter)
    {
        $grumPHP->getTaskConfiguration('securitychecker')->willReturn([]);
        $this->beConstructedWith($grumPHP, $processBuilder, $formatter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SecurityChecker::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('securitychecker');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
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
        $grumPHP->getTaskConfiguration('securitychecker')->willReturn(['run_always' => false]);

        $processBuilder->createArgumentsForCommand('security-checker')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite_if_run_always_is_true(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $grumPHP->getTaskConfiguration('securitychecker')->willReturn(['run_always' => true]);

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('security-checker')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_runs_the_suite_when_composer_has_changed_and_run_always_is_false(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $grumPHP->getTaskConfiguration('securitychecker')->willReturn(['run_always' => false]);

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('security-checker')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('composer.lock', '.', 'composer.lock')
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_an_exception_if_the_process_fails(
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context,
        ProcessFormatterInterface $formatter
    ) {
        $formatter->format($process)->willReturn('format string');

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('security-checker')->willReturn($arguments);
        $processBuilder->buildProcess(Argument::type(ProcessArgumentsCollection::class))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('composer.lock', '.', 'composer.lock')
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }
}
