<?php

namespace GrumPHP\Task;

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
     * {@inheritdoc}
     */
    public function run(array $files)
    {
        // We don't care about changed files here, we want to run the entire suit every time

        $this->processBuilder->setArguments(array(
            'php',
            $this->getCommandLocation(),
            'run',
            '--no-interaction'
        ));

        $process = $this->processBuilder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
