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

class PhpcpdSpec extends ObjectBehavior
{

    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder)
    {
        $grumPHP->getTaskConfiguration('phpcpd')->willReturn(array());
        $this->beConstructedWith($grumPHP, $processBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\Phpcpd');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('phpcpd');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver');
        $options->getDefinedOptions()->shouldContain('names');
        $options->getDefinedOptions()->shouldContain('names_exclude');
        $options->getDefinedOptions()->shouldContain('log_pmd');
        $options->getDefinedOptions()->shouldContain('exclude');
        $options->getDefinedOptions()->shouldContain('min_lines');
        $options->getDefinedOptions()->shouldContain('min_tokens');
        $options->getDefinedOptions()->shouldContain('verbose');
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
        $this->run($context)->shouldBeNull();
    }

    function it_runs_the_suite(ProcessBuilder $processBuilder, Process $process, ContextInterface $context)
    {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpcpd')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file1.php', '.', 'file1.php'),
            new SplFileInfo('file2.php', '.', 'file2.php'),
        )));
        $this->run($context);
    }

    function it_throws_exception_if_the_process_fails(
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('phpcpd')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);
        $process->getOutput()->shouldBeCalled();

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file1.php', '.', 'file1.php'),
        )));
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($context);
    }
}
