<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;

/**
 * Phpunit task
 */
class Phpunit extends AbstractExternalTask
{
    const COMMAND_NAME = 'phpunit';

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
        );
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $files = $context->getFiles()->name('*.php');
        if (0 === count($files)) {
            return;
        }

        // We don't care about changed files here, we want to run the entire suit every time
        $config = $this->getConfiguration();
        $this->processBuilder->setArguments(array(
            $this->getCommandLocation(),
        ));

        if ($config['config_file']) {
            $this->processBuilder->add('-c' . $config['config_file']);
        }

        $process = $this->processBuilder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
