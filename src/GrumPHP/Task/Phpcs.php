<?php

namespace GrumPHP\Task;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Locator\ExternalCommand;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class Phpcs
 *
 * @package GrumPHP\Task
 */
class Phpcs implements ExternalTaskInterface
{

    const COMMAND_NAME = 'phpcs';

    /**
     * @var GrumpPHP
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
        $phpcsConfig = $this->config->getPhpcs();
        foreach ($files as $file) {
            $builder = new ProcessBuilder(array('php', $this->getCommandLocation()));
            $builder->add('--standard=' . $phpcsConfig->getStandard());
            $builder->add($file);
            $process = $builder->getProcess();
            $process->run();
        }
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->config->hasPhpcs();
    }

}
