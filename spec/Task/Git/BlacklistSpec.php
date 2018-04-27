<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\IO\ConsoleIO;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Git\Blacklist;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class BlacklistSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter, ConsoleIO $consoleIO)
    {
        $grumPHP->getTaskConfiguration('git_blacklist')->willReturn([]);
        $this->beConstructedWith($grumPHP, $processBuilder, $formatter, $consoleIO);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Blacklist::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_blacklist');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('keywords');
        $options->getDefinedOptions()->shouldContain('whitelist_patterns');
        $options->getDefinedOptions()->shouldContain('triggered_by');
        $options->getDefinedOptions()->shouldContain('regexp_type');
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_does_not_do_anything_if_there_are_no_files(ProcessBuilder $processBuilder, ContextInterface $context)
    {
        $processBuilder->createArgumentsForCommand('git')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_does_not_do_anything_if_there_are_no_keywords(ProcessBuilder $processBuilder, ContextInterface $context)
    {
        $processBuilder->createArgumentsForCommand('git')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file1.php', '.', 'file1.php'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context,
        ProcessFormatterInterface $formatter,
        ConsoleIO $consoleIO
    ) {
        $formatter->format($process)->willReturn(Argument::type('string'));

        $consoleIO->isDecorated()->willReturn(false);

        $grumPHP->getTaskConfiguration('git_blacklist')->willReturn([
            'keywords'=> ['var_dump('],
        ]);

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('git')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();

        // Assume that blacklisted keywords was not found by `git grep` process
        $process->isSuccessful()->willReturn(false);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file1.php', '.', 'file1.php'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_exception_if_the_process_is_successfull(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context,
        ConsoleIO $consoleIO,
        ProcessFormatterInterface $formatter
    ) {
        $consoleIO->isDecorated()->willReturn(false);

        $formatter->format($process)->willReturn(Argument::type('string'));

        $grumPHP->getTaskConfiguration('git_blacklist')->willReturn([
            'keywords'=> ['var_dump('],
        ]);

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('git')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file1.php', '.', 'file1.php'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }
}
