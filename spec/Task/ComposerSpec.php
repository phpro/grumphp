<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Composer;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Util\Filesystem;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class ComposerSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter, Filesystem $filesystem)
    {
        $grumPHP->getTaskConfiguration('composer')->willReturn([]);
        $this->beConstructedWith($grumPHP, $processBuilder, $formatter, $filesystem);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Composer::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('composer');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('file');
        $options->getDefinedOptions()->shouldContain('no_check_all');
        $options->getDefinedOptions()->shouldContain('no_check_lock');
        $options->getDefinedOptions()->shouldContain('no_check_publish');
        $options->getDefinedOptions()->shouldContain('no_local_repository');
        $options->getDefinedOptions()->shouldContain('with_dependencies');
        $options->getDefinedOptions()->shouldContain('strict');
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_does_not_do_anything_if_there_are_no_files(ProcessBuilder $processBuilder, ContextInterface $context)
    {
        $processBuilder->createArgumentsForCommand('composer')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite(ProcessBuilder $processBuilder, Process $process, ContextInterface $context)
    {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('composer')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('composer.json', '.', 'composer.json')
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_exception_if_the_process_fails(
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context,
        ProcessFormatterInterface $formatter
    ) {
        $formatter->format($process)->willReturn('format string');

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('composer')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('composer.json', '.', 'composer.json')
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_fails_when_it_has_local_repositories(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Filesystem $filesystem,
        Process $process,
        ContextInterface $context
    ) {
        $composerFile = 'composer.json';
        $grumPHP->getTaskConfiguration('composer')->willReturn([
            'file' => $composerFile,
            'no_local_repository' => true
        ]);

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('composer')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection([
            $composerFile = new SplFileInfo($composerFile, '.', $composerFile)
        ]));

        $filesystem->readFromFileInfo($composerFile)->willReturn('{"repositories": [{"type": "path", "url": "/"}]}');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_succeeds_when_it_has_no_local_repositories(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Filesystem $filesystem,
        Process $process,
        ContextInterface $context
    ) {
        $composerFile = 'composer.json';
        $grumPHP->getTaskConfiguration('composer')->willReturn([
            'file' => $composerFile,
            'no_local_repository' => true
        ]);

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('composer')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection([
            $composerFile = new SplFileInfo($composerFile, '.', $composerFile)
        ]));

        $filesystem->readFromFileInfo($composerFile)->willReturn('{"repositories": [{"type": "vcs", "url": "/"}]}');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_succeeds_when_it_has_repositories_is_not_defined(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Filesystem $filesystem,
        Process $process,
        ContextInterface $context
    ) {
        $composerFile = 'composer.json';
        $grumPHP->getTaskConfiguration('composer')->willReturn([
            'file' => $composerFile,
            'no_local_repository' => true
        ]);

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('composer')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection([
            $composerFile = new SplFileInfo($composerFile, '.', $composerFile)
        ]));

        $filesystem->readFromFileInfo($composerFile)->willReturn('{}');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }
}
