<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\NpmScript;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

/**
 * @mixin ComposerScript
 */
class ComposerScriptSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder, ProcessFormatterInterface $formatter)
    {
        $grumPHP->getTaskConfiguration('composer_script')->willReturn(array('script' => 'test', 'working_directory' => './'));
        $this->beConstructedWith($grumPHP, $processBuilder, $formatter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\ComposerScript');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('composer_script');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver');
        $options->getDefinedOptions()->shouldContain('script');
        $options->getDefinedOptions()->shouldContain('triggered_by');
        $options->getDefinedOptions()->shouldContain('working_directory');
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
        $result->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResultInterface');
        $result->getResultCode()->shouldBe(TaskResult::SKIPPED);
    }

    function it_runs_the_suite(ProcessBuilder $processBuilder, Process $process, ContextInterface $context)
    {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('composer')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->setWorkingDirectory('./')->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('test.php', '.', 'test.php')
        )));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResultInterface');
        $result->isPassed()->shouldBe(true);
    }

    function it_throws_exception_if_the_process_fails(
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('composer')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->setWorkingDirectory('./')->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('test.php', '.', 'test.php')
        )));

        $result = $this->run($context);
        $result->shouldBeAnInstanceOf('GrumPHP\Runner\TaskResultInterface');
        $result->isPassed()->shouldBe(false);
    }
}
