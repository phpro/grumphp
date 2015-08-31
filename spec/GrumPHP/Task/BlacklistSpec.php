<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\LocatorInterface;
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
        $this->shouldHaveType('GrumPHP\Task\Blacklist');
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

    function it_does_not_do_anything_if_there_are_no_files(ProcessBuilder $processBuilder)
    {
        $processBuilder->add(Argument::any())->shouldNotBeCalled();
        $processBuilder->setArguments(Argument::any())->shouldNotBeCalled();
        $processBuilder->getProcess()->shouldNotBeCalled();

        $files = new FilesCollection();
        $this->run($files)->shouldBeNull();
    }

    function it_does_not_do_anything_if_there_are_no_keywords(ProcessBuilder $processBuilder)
    {
        $processBuilder->add(Argument::any())->shouldNotBeCalled();
        $processBuilder->setArguments(Argument::any())->shouldNotBeCalled();
        $processBuilder->getProcess()->shouldNotBeCalled();

        $files = new FilesCollection(array(
            new SplFileInfo('file1.php'),
        ));

        $this->run($files)->shouldBeNull();
    }

    function it_runs_the_suite(GrumPHP $grumPHP, LocatorInterface $externalCommandLocator, ProcessBuilder $processBuilder, Process $process)
    {
        $this->beConstructedWith($grumPHP, array('keywords'=>array('var_dump(', 'die(')), $externalCommandLocator, $processBuilder);

        $processBuilder->setArguments(Argument::type('array'))->shouldBeCalled();
        $processBuilder->add('-e var_dump(')->shouldBeCalled();
        $processBuilder->add('-e die(')->shouldBeCalled();
        $processBuilder->add('file1.php')->shouldBeCalled();
        $processBuilder->getProcess()->willReturn($process);

        $process->run()->shouldBeCalled();

        // Assume that blacklisted keywords was not found by `git grep` process
        $process->isSuccessful()->willReturn(false); 

        $files = new FilesCollection(array(
            new SplFileInfo('file1.php'),
        ));
        $this->run($files);
    }

    function it_throws_exception_if_the_process_is_successfull(GrumPHP $grumPHP, LocatorInterface $externalCommandLocator, ProcessBuilder $processBuilder, Process $process)
    {
        $this->beConstructedWith($grumPHP, array('keywords'=>array('var_dump(')), $externalCommandLocator, $processBuilder);

        $processBuilder->setArguments(Argument::type('array'))->shouldBeCalled();
        $processBuilder->add('-e var_dump(')->shouldBeCalled();
        $processBuilder->add('file1.php')->shouldBeCalled();
        $processBuilder->getProcess()->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getOutput()->shouldBeCalled();

        $files = new FilesCollection(array(
            new SplFileInfo('file1.php'),
        ));
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($files);
    }
}
