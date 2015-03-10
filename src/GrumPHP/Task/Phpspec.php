<?php

namespace GrumPHP\Task;

/**
 * Class Phpspec
 *
 * @package GrumPHP\Task
 */
class Phpspec extends AbstractExternalTask
{

    const COMMAND_NAME = 'phpspec';

    /**
     * @return string
     */
    public function getCommandLocation()
    {
        return $this->externalCommandLocator->locate(self::COMMAND_NAME);
    }

    /**
     * @param array $files
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
            throw new \RuntimeException($process->getOutput());
        }
    }

}
