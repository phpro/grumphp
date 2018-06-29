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
use GrumPHP\Task\PhpStan;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class PhpStanSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter)
    {
        $grumPHP->getTaskConfiguration('phpstan')->willReturn([]);
        $this->beConstructedWith($grumPHP, $processBuilder, $formatter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PhpStan::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('phpstan');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('autoload_file');
        $options->getDefinedOptions()->shouldContain('configuration');
        $options->getDefinedOptions()->shouldContain('ignore_patterns');
        $options->getDefinedOptions()->shouldContain('force_patterns');
        $options->getDefinedOptions()->shouldContain('level');
        $options->getDefinedOptions()->shouldContain('triggered_by');
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
        $processBuilder->buildProcess('phpstan')->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite(ProcessBuilder $processBuilder, Process $process, ContextInterface $context)
    {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpstan')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getErrorOutput()->willReturn('');
        $process->getOutput()->willReturn('');

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('test.php', '.', 'test.php')
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_runs_the_suite_with_ignored_files(ProcessBuilder $processBuilder, Process $process, ContextInterface $context, GrumPHP $grumPHP)
    {
        $grumPHP->getTaskConfiguration('phpstan')->willReturn([
            'ignore_patterns' => ['TaskResultCollection.php'],
        ]);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('src/Collection/TaskResultCollection.php', 'src/Collection', 'TaskResultCollection.php'),
            new SplFileInfo('src/Collection/TaskResultCollection.php', 'src/Collection', 'Passed.php'),
        ]));

        $processBuilder->buildProcess('phpstan')->shouldNotBeCalled();

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpstan')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getErrorOutput()->willReturn('');
        $process->getOutput()->willReturn('');

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::PASSED);
    }

    function it_runs_the_suite_with_forced_files(ProcessBuilder $processBuilder, Process $process, ContextInterface $context, GrumPHP $grumPHP)
    {
        $grumPHP->getTaskConfiguration('phpstan')->willReturn([
            'ignore_patterns' => ['TaskResultCollection.php'],
            'force_patterns' => ['TaskResultCollection.php'],
        ]);

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpstan')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getErrorOutput()->willReturn('');
        $process->getOutput()->willReturn('');

        $context->getFiles()->willReturn(new FilesCollection([
                new SplFileInfo('src/Collection/TaskResultCollection.php', 'src/Collection', 'TaskResultCollection.php')])
        );

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::PASSED);
    }

    function it_throws_exception_if_the_process_fails(
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context,
        ProcessFormatterInterface $formatter
    ) {
        $formatter->format($process)->willReturn('format string');

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpstan')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('src/Collection/TaskResultCollection.php', 'src/Collection', 'TaskResultCollection.php'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }
}
