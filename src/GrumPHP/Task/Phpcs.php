<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;

/**
 * Phpcs task
 */
class Phpcs extends AbstractExternalTask
{
    const COMMAND_NAME = 'phpcs';

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
        if (0 === count($files)) {
            return;
        }

        $this->processBuilder->setArguments(array(
            'php',
            $this->getCommandLocation(),
            '--standard=' . $this->getConfiguration()->getStandard(),
            '--warning-severity=0', // TODO: caring about warnings should be configurable, but for now it's just annoying
        ));

        foreach ($files as $file) {
            $this->processBuilder->add($file);
        }

        $process = $this->processBuilder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
