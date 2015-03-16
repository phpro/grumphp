<?php

namespace spec\GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\LocatorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class PhpcsSpec extends ObjectBehavior
{
    /**
     * @param array $files
     *
     * @return Finder
     */
    protected function mockFinder(array $files)
    {
        $finder = new Finder();
        $finder->append($files);
        return $finder;
    }

    function let(GrumPHP $grumPHP, LocatorInterface $externalCommandLocator, ProcessBuilder $processBuilder)
    {
        $this->beConstructedWith($grumPHP, array(), $externalCommandLocator, $processBuilder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Task\Phpcs');
    }

    function it_is_a_grumphp_external_task()
    {
        $this->shouldHaveType('GrumPHP\Task\ExternalTaskInterface');
    }

    function it_uses_its_external_command_locator_to_find_correct_command(LocatorInterface $externalCommandLocator)
    {
        $externalCommandLocator->locate('phpcs')->shouldBeCalled();
        $this->getCommandLocation();
    }

    function it_does_not_do_anything_if_there_are_no_files(ProcessBuilder $processBuilder)
    {
        $processBuilder->add(Argument::any())->shouldNotBeCalled();
        $processBuilder->setArguments(Argument::any())->shouldNotBeCalled();
        $processBuilder->getProcess()->shouldNotBeCalled();

        $finder = $this->mockFinder(array());
        $this->run($finder)->shouldBeNull();
    }

    function it_runs_the_suite(ProcessBuilder $processBuilder, Process $process)
    {
        $processBuilder->add('file1')->shouldBeCalled();
        $processBuilder->add('file2')->shouldBeCalled();
        $processBuilder->setArguments(Argument::type('array'))->shouldBeCalled();
        $processBuilder->getProcess()->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);

        $finder = $this->mockFinder(array('file1', 'file2'));
        $this->run($finder);
    }

    function it_throws_exception_if_the_process_fails(ProcessBuilder $processBuilder, Process $process)
    {
        $processBuilder->add('file1')->shouldBeCalled();
        $processBuilder->setArguments(Argument::type('array'))->shouldBeCalled();
        $processBuilder->getProcess()->willReturn($process);

        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(false);
        $process->getOutput()->shouldBeCalled();

        $finder = $this->mockFinder(array('file1'));
        $this->shouldThrow('GrumPHP\Exception\RuntimeException')->duringRun($finder);
    }
}
