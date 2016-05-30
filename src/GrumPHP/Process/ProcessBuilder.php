<?php

namespace GrumPHP\Process;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
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
     * ProcessBuilder constructor.
     *
     * @param GrumPHP         $config
     * @param ExternalCommand $externalCommandLocator
     */
    public function __construct(GrumPHP $config, ExternalCommand $externalCommandLocator)
    {
        $this->externalCommandLocator = $externalCommandLocator;
        $this->config = $config;
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

        return $builder->getProcess();
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
}
