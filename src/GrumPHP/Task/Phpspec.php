<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\Finder\Finder;

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
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function run(Finder $files)
    {
        $files->name('*.php');
        if (0 === count($files)) {
            return;
        }

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
