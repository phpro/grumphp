<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Exception\RuntimeException;

/**
 * Blacklist task
 *
 * @author  Igor Mukhin <igor.mukhin@gmail.com>
 */
class Blacklist extends AbstractExternalTask
{
    const COMMAND_NAME = 'git';

    /**
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return array(
            'keywords' => null,
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
    public function run(FilesCollection $files)
    {
        $files = $files->name('*.php');
        if (0 === count($files)) {
            return;
        }

        $config = $this->getConfiguration();
        if (empty($config['keywords'])) {
            return;
        }

        $this->processBuilder->setArguments(array(
            'git',
            'grep',
            '--cached',
            '-n'
        ));

        foreach ($config['keywords'] as $keyword) {
            $this->processBuilder->add(sprintf('-e %s', $keyword));
        }

        foreach ($files as $file) {
            $this->processBuilder->add($file);
        }

        $process = $this->processBuilder->getProcess();
        $process->run();

        if ($process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                "You have blacklisted keywords in your commit:\n%s",
                $process->getOutput()
            ));
        }
    }
}
