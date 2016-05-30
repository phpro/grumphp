<?php

namespace spec\GrumPHP\Process;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\ExternalCommand;
use GrumPHP\Process\ProcessBuilder;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin ProcessBuilder
 */
class ProcessBuilderSpec extends ObjectBehavior
{
    function let(GrumPHP $config, ExternalCommand $externalCommandLocator)
    {
        $this->beConstructedWith($config, $externalCommandLocator);
        $config->getProcessTimeout()->willReturn(60);
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
        $process->getCommandLine()->shouldBeQuoted('/usr/bin/grumphp');
    }

    function it_should_be_possible_to_configure_the_process_timeout(
        GrumPHP $config,
        ExternalCommand $externalCommandLocator
    )
    {
        $config->getProcessTimeout()->willReturn(120);

        $arguments = new ProcessArgumentsCollection(array('/usr/bin/grumphp'));
        $process = $this->buildProcess($arguments);
        $process->getTimeout()->shouldBe(120.0);
    }

    function getMatchers()
    {
        return array(
            'beQuoted' => function ($subject, $string) {
                $regex = sprintf('{^([\'"])%s\1$}', preg_quote($string));
                if (!preg_match($regex, $subject)) {
                    throw new FailureException(sprintf(
                        'Expected a quoted version of %s, got %s.',
                        $string, $subject
                    ));
                }

                return true;
            }
        );
    }
}
