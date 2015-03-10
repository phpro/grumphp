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
        $phpcsConfig = $this->config->getConfiguration('phpcs'); // TODO: task should have access to config
        foreach ($files as $file) {
            $suffix = substr($file, strlen($file) - 8);

            if ('Spec.php' === $suffix || 'Test.php' === $suffix) {
                continue;
            }

            $builder = new ProcessBuilder(array('php', $this->getCommandLocation()));
            $builder->add('--standard=' . $phpcsConfig->getStandard());
            $builder->add($file);
            $process = $builder->getProcess();
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getOutput());
            }
        }
    }

}
