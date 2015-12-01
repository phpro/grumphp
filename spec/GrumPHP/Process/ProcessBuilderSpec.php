<?php

namespace spec\GrumPHP\Process;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Locator\ExternalCommand;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProcessBuilderSpec extends ObjectBehavior
{
    function let(ExternalCommand $externalCommandLocator)
    {
        $this->beConstructedWith($externalCommandLocator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Process\ProcessBuilder');
    }

    function it_should_be_able_to_create_process_arguments_based_on_taskname(ExternalCommand $externalCommandLocator)
    {
        $externalCommandLocator->locate('grumphp')->willReturn('/usr/bin/grumphp');

        $arguments = $this->createArgumentsForCommand('grumphp');
        $arguments->shouldHaveType('GrumPHP\Collection\ProcessArgumentsCollection');
        $arguments[0]->shouldBe('/usr/bin/grumphp');
        $arguments->count()->shouldBe(1);
    }

    function it_should_build_process_based_on_process_arguments()
    {
        $arguments = new ProcessArgumentsCollection(array('/usr/bin/grumphp'));
        $process = $this->buildProcess($arguments);

        $process->shouldHaveType('Symfony\Component\Process\Process');
        $process->getCommandLine()->shouldBe("'/usr/bin/grumphp'");
    }
}
