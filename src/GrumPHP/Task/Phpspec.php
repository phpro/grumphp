<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Exception\RuntimeException;

/**
 * Phpspec task
 */
class Phpspec extends AbstractExternalTask
{
    const COMMAND_NAME = 'phpspec';

    /**
     * {@inheritdoc}
     */
    public function getCommandLocation()
    {
        return $this->externalCommandLocator->locate(self::COMMAND_NAME);
    }

    /**
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return array(
            'config_file' => null,
            'stop_on_failure' => false,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function run(FilesCollection $files)
    {
        $files = $files->name('*.php');
        if (0 === count($files)) {
            return;
        }

        // We don't care about changed files here, we want to run the entire suit every time
        $config = $this->getConfiguration();
        $this->processBuilder->setArguments(array(
            'php',
            $this->getCommandLocation(),
            'run',
            '--no-interaction'
        ));

        if ($config['config_file']) {
            $this->processBuilder->add('--config=' . $config['config_file']);
        }

        if ($config['stop_on_failure']) {
            $this->processBuilder->add('--stop-on-failure');
        }

        $process = $this->processBuilder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
