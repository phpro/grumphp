<?php

namespace spec\GrumPHP\Process;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\IO\IOInterface;
use GrumPHP\Locator\ExternalCommand;
use GrumPHP\Process\ProcessBuilder;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Process\Process;
use GrumPHP\Process\ProcessUtils;

class ProcessBuilderSpec extends ObjectBehavior
{
    function let(GrumPHP $config, ExternalCommand $externalCommandLocator, IOInterface $io)
    {
        $this->beConstructedWith($config, $externalCommandLocator, $io);
        $config->getProcessTimeout()->willReturn(60);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProcessBuilder::class);
    }

    function it_should_be_able_to_create_process_arguments_based_on_taskname(ExternalCommand $externalCommandLocator)
    {
        $externalCommandLocator->locate('grumphp', false)->willReturn('/usr/bin/grumphp');

        $arguments = $this->createArgumentsForCommand('grumphp', false);
        $arguments->shouldHaveType(ProcessArgumentsCollection::class);
        $arguments[0]->shouldBe('/usr/bin/grumphp');
        $arguments->count()->shouldBe(1);
    }

    function it_should_be_able_to_create_process_arguments_based_on_taskname_and_force_unix_path(ExternalCommand $externalCommandLocator)
    {
        $externalCommandLocator->locate('grumphp', true)->willReturn('/usr/bin/grumphp');

        $arguments = $this->createArgumentsForCommand('grumphp', true);
        $arguments->shouldHaveType(ProcessArgumentsCollection::class);
        $arguments[0]->shouldBe('/usr/bin/grumphp');
        $arguments->count()->shouldBe(1);
    }

    function it_should_build_process_based_on_process_arguments(IOInterface $io)
    {
        $io->isVeryVerbose()->willReturn(false);

        $arguments = new ProcessArgumentsCollection(['/usr/bin/grumphp']);
        $process = $this->buildProcess($arguments);

        $process->shouldHaveType(Process::class);
        $process->getCommandLine()->shouldBeQuoted('/usr/bin/grumphp');
    }

    function it_should_be_possible_to_configure_the_process_timeout(GrumPHP $config, IOInterface $io)
    {
        $io->isVeryVerbose()->willReturn(false);

        $config->getProcessTimeout()->willReturn(120);

        $arguments = new ProcessArgumentsCollection(['/usr/bin/grumphp']);
        $process = $this->buildProcess($arguments);
        $process->getTimeout()->shouldBe(120.0);
    }

    function it_outputs_the_command_when_run_very_very_verbose(IOInterface $io)
    {
        $io->isVeryVerbose()->willReturn(true);

        $command = '/usr/bin/grumphp';
        $io->write([PHP_EOL . 'Command: ' . ProcessUtils::escapeArgument($command)], true)->shouldBeCalled();

        $arguments = new ProcessArgumentsCollection([$command]);
        $this->buildProcess($arguments);
    }

    function getMatchers()
    {
        return [
            'beQuoted' => function ($subject, $string) {
                $regex = sprintf('{^([\'"])%s\1$}', preg_quote($string));
                if (!preg_match($regex, $subject)) {
                    throw new FailureException(sprintf(
                        'Expected a quoted version of %s, got %s.',
                        $string,
                        $subject
                    ));
                }

                return true;
            }
        ];
    }
}
