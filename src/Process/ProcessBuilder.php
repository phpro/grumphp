<?php

namespace GrumPHP\Process;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Exception\PlatformException;
use GrumPHP\IO\IOInterface;
use GrumPHP\Locator\ExternalCommand;
use GrumPHP\Util\Platform;
use Symfony\Component\Process\Process;

class ProcessBuilder
{
    /**
     * @var ExternalCommand
     */
    private $externalCommandLocator;

    /**
     * @var GrumPHP
     */
    private $config;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * ProcessBuilder constructor.
     *
     * @param GrumPHP         $config
     * @param ExternalCommand $externalCommandLocator
     */
    public function __construct(GrumPHP $config, ExternalCommand $externalCommandLocator, IOInterface $io)
    {
        $this->externalCommandLocator = $externalCommandLocator;
        $this->config = $config;
        $this->io = $io;
    }

    /**
     * @param string $command
     *
     * @return ProcessArgumentsCollection
     */
    public function createArgumentsForCommand($command)
    {
        $executable = $this->getCommandLocation($command);

        return ProcessArgumentsCollection::forExecutable($executable);
    }

    /**
     * @param ProcessArgumentsCollection $arguments
     *
     * @return Process
     * @throws \GrumPHP\Exception\PlatformException
     */
    public function buildProcess(ProcessArgumentsCollection $arguments)
    {
        $process = ProcessFactory::fromArguments($arguments);
        $process->setTimeout($this->config->getProcessTimeout());

        $this->logProcessInVerboseMode($process);
        $this->guardWindowsCmdMaxInputStringLimitation($process);

        return $process;
    }

    /**
     * @param Process $process
     *
     * @throws \GrumPHP\Exception\PlatformException
     */
    private function guardWindowsCmdMaxInputStringLimitation(Process $process)
    {
        if (!Platform::isWindows()) {
            return;
        }

        if (strlen($process->getCommandLine()) <= Platform::WINDOWS_COMMANDLINE_STRING_LIMITATION) {
            return;
        }

        throw PlatformException::commandLineStringLimit($process);
    }

    /**
     * @param string $command
     *
     * @return string
     */
    private function getCommandLocation($command)
    {
        return $this->externalCommandLocator->locate($command);
    }

    /**
     * @param Process $process
     */
    private function logProcessInVerboseMode(Process $process)
    {
        if ($this->io->isVeryVerbose()) {
            $this->io->write(PHP_EOL . 'Command: ' . $process->getCommandLine(), true);
        }
    }
}
