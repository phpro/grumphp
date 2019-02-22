<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\PhpcsFormatter;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Phpcs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class PhpcsSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder, PhpcsFormatter $formatter)
    {
        $grumPHP->getTaskConfiguration('phpcs')->willReturn([]);
        $this->beConstructedWith($grumPHP, $processBuilder, $formatter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Phpcs::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('phpcs');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('standard');
        $options->getDefinedOptions()->shouldContain('severity');
        $options->getDefinedOptions()->shouldContain('error_severity');
        $options->getDefinedOptions()->shouldContain('warning_severity');
        $options->getDefinedOptions()->shouldContain('tab_width');
        $options->getDefinedOptions()->shouldContain('encoding');
        $options->getDefinedOptions()->shouldContain('whitelist_patterns');
        $options->getDefinedOptions()->shouldContain('ignore_patterns');
        $options->getDefinedOptions()->shouldContain('sniffs');
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

    function it_does_not_do_anything_if_there_are_no_files(
        ProcessBuilder $processBuilder,
        ContextInterface $context
    ) {
        $processBuilder->buildProcess('phpcs')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_does_not_runs_the_suite_with_invalid_extensions(
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpcs')->willReturn($arguments);
        $processBuilder->createArgumentsForCommand('phpcbf')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldNotBeCalled();

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file1.txt', '.', 'file1.txt'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite(
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpcs')->willReturn($arguments);
        $processBuilder->createArgumentsForCommand('phpcbf')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file1.php', '.', 'file1.php'),
            new SplFileInfo('file2.php', '.', 'file2.php'),
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
        $formatter->format($process)->willReturn(Argument::type('string'));

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpcs')->willReturn($arguments);
        $processBuilder->createArgumentsForCommand('phpcbf')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file1.php', '.', 'file1.php'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }
}
