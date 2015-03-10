<?php

namespace GrumPHP\Task;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\ExternalCommand;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class Phpspec
 *
 * @package GrumPHP\Task
 */
class Phpspec implements ExternalTaskInterface
{

    const COMMAND_NAME = 'phpspec';

    /**
     * @var GrumPHP
     */
    private $config;

    /**
     * @param GrumPHP $config
     */
    public function __construct(GrumPHP $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getCommandLocation()
    {
        $locator = new ExternalCommand($this->config->getBaseDir());
        return $locator->locate(self::COMMAND_NAME);
    }

    /**
     * @param array $files
     */
    public function run(array $files)
    {
        // We don't care about changed files here, we want to run the entire suit every time

        $builder = new ProcessBuilder(array('php', $this->getCommandLocation(), 'run', '--no-interaction'));
        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getOutput());
        }
    }

}
