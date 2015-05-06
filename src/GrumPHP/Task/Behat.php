<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Exception\RuntimeException;

/**
 * Behat task
 */
class Behat extends AbstractExternalTask
{
    const COMMAND_NAME = 'behat';

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
            'config' => null,
            'format' => null,
            'suite' => null,
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
            $this->getCommandLocation(),
        ));

        if ($config['config']) {
            $this->processBuilder->add('--config=' . $config['config']);
        }

        if ($config['format']) {
            $this->processBuilder->add('--format=' . $config['format']);
        }

        if ($config['suite']) {
            $this->processBuilder->add('--suite=' . $config['suite']);
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
