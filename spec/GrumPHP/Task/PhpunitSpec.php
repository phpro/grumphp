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

class PhpunitSpec extends ObjectBehavior
{
    function let(GrumPHP $grumPHP, LocatorInterface $externalCommandLocator, ProcessBuilder $processBuilder)
    {
        $this->beConstructedWith($grumPHP, array(), $externalCommandLocator, $processBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\Phpunit');
    }

    function it_is_a_grumphp_external_task()
    {
        $this->shouldHaveType('GrumPHP\Task\ExternalTaskInterface');
    }

    function it_is_enabled_by_default()
    {
        $this->isEnabled()->shouldBe(true);
    }

    function it_uses_its_external_command_locator_to_find_correct_command(LocatorInterface $externalCommandLocator)
    {
        $externalCommandLocator->locate('phpunit')->shouldBeCalled();
        $this->getCommandLocation();
    }

    function it_runs_the_suite(ProcessBuilder $processBuilder, Process $process)
    {
        $processBuilder->setArguments(Argument::type('array'))->shouldBeCalled();
        $processBuilder->add('-cphpunit.xml')->shouldBeCalled();
        $processBuilder->getProcess()->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $files = new FilesCollection(array(
            new SplFileInfo('test.php')
        ));
        $this->run($files);
    }

    function it_throws_exception_if_the_process_fails(ProcessBuilder $processBuilder, Process $process)
    {
        $processBuilder->setArguments(Argument::type('array'))->shouldBeCalled();
        $processBuilder->add('-cphpunit.xml')->shouldBeCalled();
        $processBuilder->getProcess()->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);
        $process->getOutput()->shouldBeCalled();

        $files = new FilesCollection(array(
            new SplFileInfo('test.php')
        ));
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($files);
    }
}
