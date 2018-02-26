<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\PhpCsFixerFormatter;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpCsFixerV2;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class PhpCsFixerV2Spec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder, PhpCsFixerFormatter $formatter)
    {
        $grumPHP->getTaskConfiguration('phpcsfixer2')->willReturn([]);

        $formatter->format(Argument::any())->willReturn('');
        $formatter->formatSuggestion(Argument::any())->willReturn('');
        $formatter->formatErrorMessage(Argument::cetera())->willReturn('');

        $this->beConstructedWith($grumPHP, $processBuilder, $formatter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PhpCsFixerV2::class);
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('phpcsfixer2');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf(OptionsResolver::class);
        $options->getDefinedOptions()->shouldContain('allow_risky');
        $options->getDefinedOptions()->shouldContain('cache_file');
        $options->getDefinedOptions()->shouldContain('config');
        $options->getDefinedOptions()->shouldContain('rules');
        $options->getDefinedOptions()->shouldContain('using_cache');
        $options->getDefinedOptions()->shouldContain('config_contains_finder');
        $options->getDefinedOptions()->shouldContain('verbose');
        $options->getDefinedOptions()->shouldContain('diff');
        $options->getDefinedOptions()->shouldContain('triggered_by');
    }

    function it_does_not_do_anything_if_there_are_no_files(ProcessBuilder $processBuilder, ContextInterface $context)
    {
        $processBuilder->createArgumentsForCommand('php-cs-fixer')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();
        $context->getFiles()->willReturn(new FilesCollection());

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_should_run_in_run_context(RunContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_runs_phpcsfixer2_on_finder_in_run_context_with_finder_config(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        RunContext $context,
        PhpCsFixerFormatter $formatter
    ) {
        $grumPHP->getTaskConfiguration('phpcsfixer2')->willReturn([
            'config' => '.php_cs',
            'config_contains_finder' => true,
        ]);
        $formatter->resetCounter()->shouldBeCalled();

        $context->getFiles()->willReturn(new FilesCollection([
            $file1 = new SplFileInfo('file1.php', '.', 'file1.php'),
            $file2 = new SplFileInfo('file2.php', '.', 'file2.php'),
        ]));

        $processBuilder->createArgumentsForCommand('php-cs-fixer')->willReturn(new ProcessArgumentsCollection());
        $processBuilder->buildProcess(Argument::that(function (ProcessArgumentsCollection $args) use ($file1, $file2) {
            return !$args->contains($file1->getPathname())
                && !$args->contains($file2->getPathname());
        }))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_runs_phpcsfixer2_on_all_files_in_run_context_without_finder_config(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        RunContext $context,
        PhpCsFixerFormatter $formatter
    ) {
        $grumPHP->getTaskConfiguration('phpcsfixer2')->willReturn([
            'config' => '.php_cs',
            'config_contains_finder' => false,
        ]);
        $formatter->resetCounter()->shouldBeCalled();

        $context->getFiles()->willReturn(new FilesCollection([
            $file1 = new SplFileInfo('file1.php', '.', 'file1.php'),
            $file2 = new SplFileInfo('file2.php', '.', 'file2.php'),
        ]));

        $processBuilder->createArgumentsForCommand('php-cs-fixer')->willReturn(new ProcessArgumentsCollection());
        $processBuilder->buildProcess(Argument::that(function (ProcessArgumentsCollection $args) use ($file1, $file2) {
            return !$args->contains('--path-mode=intersection')
                && $args->contains($file1->getPathname())
                && $args->contains($file2->getPathname());
        }))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_runs_the_suite_for_changed_files_on_pre_commit(
        ProcessBuilder $processBuilder,
        Process $process,
        GitPreCommitContext $context,
        PhpCsFixerFormatter $formatter
    ) {
        $formatter->resetCounter()->shouldBeCalled();
        $context->getFiles()->willReturn(new FilesCollection([
            $file1 = new SplFileInfo('file1.php', '.', 'file1.php'),
            $file2 = new SplFileInfo('file2.php', '.', 'file2.php'),
        ]));

        $processBuilder->createArgumentsForCommand('php-cs-fixer')->willReturn(new ProcessArgumentsCollection());
        $processBuilder->buildProcess(Argument::that(function (ProcessArgumentsCollection $args) use ($file1, $file2) {
            return $args->contains($file1->getPathname()) && $args->contains($file2->getPathname());
        }))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_exception_if_the_process_fails(
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context,
        PhpCsFixerFormatter $formatter
    ) {
        $formatter->resetCounter()->shouldBeCalled();

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('php-cs-fixer')->willReturn($arguments);
        $processBuilder->buildProcess(Argument::type(ProcessArgumentsCollection::class))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);

        $context->getFiles()->willReturn(new FilesCollection([
            new SplFileInfo('file1.php', '.', 'file1.php'),
        ]));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(false);
    }

    function it_composes_a_rule_list(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        RunContext $context,
        PhpCsFixerFormatter $formatter
    ) {
        $formatter->resetCounter()->shouldBeCalled();
        $grumPHP->getTaskConfiguration('phpcsfixer2')->willReturn([
            'config' => 'foo',
            'rules' => ['foo', 'bar'],
        ]);
        $context->getFiles()->willReturn(new FilesCollection([new SplFileInfo('file1.php', '.', 'file1.php')]));

        $processBuilder->createArgumentsForCommand('php-cs-fixer')->willReturn(new ProcessArgumentsCollection);
        $processBuilder->buildProcess(Argument::that(
            function (ProcessArgumentsCollection $args) {
                return $args->contains('--rules=foo,bar');
            }
        ))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $this->run($context)->isPassed()->shouldBe(true);
    }

    function it_composes_a_rule_dictionary(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        RunContext $context,
        PhpCsFixerFormatter $formatter
    ) {
        $formatter->resetCounter()->shouldBeCalled();
        $grumPHP->getTaskConfiguration('phpcsfixer2')->willReturn([
            'config' => 'foo',
            'rules' => $rules = [
                'foo' => [
                    'bar',
                ],
            ],
        ]);
        $context->getFiles()->willReturn(new FilesCollection([new SplFileInfo('file1.php', '.', 'file1.php')]));

        $processBuilder->createArgumentsForCommand('php-cs-fixer')->willReturn(new ProcessArgumentsCollection);
        $processBuilder->buildProcess(Argument::that(
            function (ProcessArgumentsCollection $args) use ($rules) {
                return $args->contains('--rules={"foo":["bar"]}');
            }
        ))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $this->run($context)->isPassed()->shouldBe(true);
    }

    function it_does_not_apply_optional_arguments_if_not_passed(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        RunContext $context,
        PhpCsFixerFormatter $formatter
    ) {
        $formatter->resetCounter()->shouldBeCalled();
        $grumPHP->getTaskConfiguration('phpcsfixer2')->willReturn([]);
        $context->getFiles()->willReturn(new FilesCollection([new SplFileInfo('file1.php', '.', 'file1.php')]));

        $processBuilder->createArgumentsForCommand('php-cs-fixer')->willReturn(new ProcessArgumentsCollection());
        $processBuilder->buildProcess(Argument::that(function (ProcessArgumentsCollection $arguments) {
            return !$arguments->contains('--allow-risky') && !$arguments->contains('--using-cache');
        }))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }

    function it_applies_optional_arguments_if_passed(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        RunContext $context,
        PhpCsFixerFormatter $formatter
    ) {
        $formatter->resetCounter()->shouldBeCalled();
        $grumPHP->getTaskConfiguration('phpcsfixer2')->willReturn([
            'allow_risky' => true,
            'using_cache' => false,
        ]);
        $context->getFiles()->willReturn(new FilesCollection([new SplFileInfo('file1.php', '.', 'file1.php')]));

        $processBuilder->createArgumentsForCommand('php-cs-fixer')->willReturn(new ProcessArgumentsCollection());
        $processBuilder->buildProcess(Argument::that(function (ProcessArgumentsCollection $arguments) {
            return $arguments->contains('--allow-risky=yes') && $arguments->contains('--using-cache=no');
        }))->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf(TaskResultInterface::class);
        $result->isPassed()->shouldBe(true);
    }
}
