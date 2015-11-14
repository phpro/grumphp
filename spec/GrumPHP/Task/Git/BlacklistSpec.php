<?php

namespace spec\GrumPHP\Task\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\LocatorInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use SplFileInfo;

class BlacklistSpec extends ObjectBehavior
{

    function let(GrumPHP $grumPHP, LocatorInterface $externalCommandLocator, ProcessBuilder $processBuilder)
    {
        $this->beConstructedWith($grumPHP, array(), $externalCommandLocator, $processBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\Git\Blacklist');
    }

    function it_is_a_grumphp_external_task()
    {
        $this->shouldHaveType('GrumPHP\Task\ExternalTaskInterface');
    }

    function it_uses_its_external_command_locator_to_find_correct_command(LocatorInterface $externalCommandLocator)
    {
        $externalCommandLocator->locate('git')->shouldBeCalled();
        $this->getCommandLocation();
    }

    function it_should_run_in_git_pre_commit_context(GitPreCommitContext $context)
    {
        $this->canRunInContext($context)->shouldReturn(true);
    }

    function it_does_not_do_anything_if_there_are_no_files(ProcessBuilder $processBuilder, ContextInterface $context)
    {
        $processBuilder->add(Argument::any())->shouldNotBeCalled();
        $processBuilder->setArguments(Argument::any())->shouldNotBeCalled();
        $processBuilder->getProcess()->shouldNotBeCalled();

        $context->getFiles()->willReturn(new FilesCollection());
        $this->run($context)->shouldBeNull();
    }

    function it_does_not_do_anything_if_there_are_no_keywords(ProcessBuilder $processBuilder, ContextInterface $context)
    {
        $processBuilder->add(Argument::any())->shouldNotBeCalled();
        $processBuilder->setArguments(Argument::any())->shouldNotBeCalled();
        $processBuilder->getProcess()->shouldNotBeCalled();

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file1.php'),
        )));

        $this->run($context)->shouldBeNull();
    }

    function it_runs_the_suite(
        GrumPHP $grumPHP,
        LocatorInterface $externalCommandLocator,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $this->beConstructedWith($grumPHP, array('keywords'=>array('var_dump(')), $externalCommandLocator, $processBuilder);

        $processBuilder->setArguments(Argument::type('array'))->shouldBeCalled();
        $processBuilder->getProcess()->willReturn($process);

        $process->run()->shouldBeCalled();

        // Assume that blacklisted keywords was not found by `git grep` process
        $process->isSuccessful()->willReturn(false); 

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file1.php'),
        )));
        $this->run($context);
    }

    function it_throws_exception_if_the_process_is_successfull(
        GrumPHP $grumPHP,
        LocatorInterface $externalCommandLocator,
        ProcessBuilder $processBuilder,
        Process $process,
        ContextInterface $context
    ) {
        $this->beConstructedWith($grumPHP, array('keywords'=>array('var_dump(')), $externalCommandLocator, $processBuilder);

        $processBuilder->setArguments(Argument::type('array'))->shouldBeCalled();
        $processBuilder->getProcess()->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getOutput()->shouldBeCalled();

        $context->getFiles()->willReturn(new FilesCollection(array(
            new SplFileInfo('file1.php'),
        )));
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($context);
    }
}
