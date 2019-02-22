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
use GrumPHP\Task\PhpCpd;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class PhpCpdSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter)
    {
        $grumPHP->getTaskConfiguration('phpcpd')->willReturn([]);
        $this->beConstructedWith($grumPHP, $processBuilder, $formatter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PhpCpd::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('phpcpd');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('directory');
        $options->getDefinedOptions()->shouldContain('exclude');
        $options->getDefinedOptions()->shouldContain('names_exclude');
        $options->getDefinedOptions()->shouldContain('regexps_exclude');
        $options->getDefinedOptions()->shouldContain('fuzzy');
        $options->getDefinedOptions()->shouldContain('min_lines');
        $options->getDefinedOptions()->shouldContain('min_tokens');
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
        $processBuilder->buildProcess('phpcpd')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite(ProcessBuilder $processBuilder, Process $process, ContextInterface $context)
    {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpcpd')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('test.php', '.', 'test.php')
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
        $processBuilder->createArgumentsForCommand('phpcpd')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('test.php', '.', 'test.php')
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_does_not_apply_regexps_exclude_option_if_it_is_not_passed(
        ProcessBuilder $processBuilder,
        Process $process,
        RunContext $context
    ) {
        $context->getFiles()->willReturn(new FilesCollection([new SplFileInfo('file1.php', '.', 'file1.php')]));

        $processBuilder->createArgumentsForCommand('phpcpd')->willReturn(new ProcessArgumentsCollection());
        $processBuilder->buildProcess(Argument::that(function (ProcessArgumentsCollection $arguments) {
            return !$arguments->contains('--regexps-exclude');
        }))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_applies_regexps_exclude_option_if_it_is_passed(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        RunContext $context
    ) {
        $grumPHP->getTaskConfiguration('phpcpd')->willReturn([
            'regexps_exclude' => ['path/to/foo.php', 'path/to/bar.php'],
        ]);

        $context->getFiles()->willReturn(new FilesCollection([new SplFileInfo('file1.php', '.', 'file1.php')]));

        $processBuilder->createArgumentsForCommand('phpcpd')->willReturn(new ProcessArgumentsCollection());
        $processBuilder->buildProcess(Argument::that(function (ProcessArgumentsCollection $arguments) {
            return $arguments->contains('--regexps-exclude=path/to/foo.php,path/to/bar.php');
        }))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }
}
