<?php

namespace GrumPHP\Process;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\IO\IOInterface;
use GrumPHP\Locator\ExternalCommand;
use Symfony\Component\Process\Process;
use \Symfony\Component\Process\ProcessBuilder as SymfonyProcessBuilder;

/**
 * Class ProcessBuilder
 *
 * @package GrumPHP\Process
 */
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
     */
    public function buildProcess(ProcessArgumentsCollection $arguments)
    {
        $builder = SymfonyProcessBuilder::create($arguments->getValues());
        $builder->setTimeout($this->config->getProcessTimeout());
        $process = $builder->getProcess();
        $this->logProcessInVerboseMode($process);
        return $process;
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
    private function logProcessInVerboseMode($process)
    {
        if ($this->io->isVeryVerbose()) {
            $this->io->write(PHP_EOL . 'Command: ' . $process->getCommandLine(), true);
        }
    }
}
