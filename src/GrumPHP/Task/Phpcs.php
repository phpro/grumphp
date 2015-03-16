<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\Finder\Finder;

/**
 * Phpcs task
 */
class Phpcs extends AbstractExternalTask
{
    const COMMAND_NAME = 'phpcs';

    /**
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return array(
            'standard' => 'PSR2',
        );
    }

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
    public function run(Finder $files)
    {
        $files->name('*.php');
        if (0 === count($files)) {
            return;
        }

        $config = $this->getConfiguration();
        $this->processBuilder->setArguments(array(
            'php',
            $this->getCommandLocation(),
            '--standard=' . $config['standard'],
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
