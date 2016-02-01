<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

class BlacklistSpec extends ObjectBehavior
{

    function let(GrumPHP $grumPHP, ProcessBuilder $processBuilder)
    {
        $grumPHP->getTaskConfiguration('git_blacklist')->willReturn(array());
        $this->beConstructedWith($grumPHP, $processBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\Git\Blacklist');
    }

    function it_should_have_a_name()
    {
        $this->getName()->shouldBe('git_blacklist');
    }

    function it_should_have_configurable_options()
    {
        $options = $this->getConfigurableOptions();
        $options->shouldBeAnInstanceOf('Symfony\Component\OptionsResolver\OptionsResolver');
        $options->getDefinedOptions()->shouldContain('keywords');
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
        $this->run($context)->shouldBeNull();
    }

    function it_does_not_do_anything_if_there_are_no_keywords(ProcessBuilder $processBuilder, ContextInterface $context)
    {
        $processBuilder->createArgumentsForCommand('git')->shouldNotBeCalled();
        $processBuilder->buildProcess()->shouldNotBeCalled();

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file1.php', '.', 'file1.php'),
        )));

        $this->run($context)->shouldBeNull();
    }

    function it_runs_the_suite(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $grumPHP->getTaskConfiguration('git_blacklist')->willReturn(array(
            'keywords'=>array('var_dump('))
        );

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('git')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();

        // Assume that blacklisted keywords was not found by `git grep` process
        $process->isSuccessful()->willReturn(false);

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file1.php', '.', 'file1.php'),
        )));
        $this->run($context);
    }

    function it_throws_exception_if_the_process_is_successfull(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $grumPHP->getTaskConfiguration('git_blacklist')->willReturn(array(
            'keywords'=>array('var_dump('))
        );

        $arguments = new ProcessArgumentsCollection();
        $processBuilder->createArgumentsForCommand('git')->willReturn($arguments);
        $processBuilder->buildProcess($arguments)->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getOutput()->shouldBeCalled();

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file1.php', '.', 'file1.php'),
        )));
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($context);
    }
}
