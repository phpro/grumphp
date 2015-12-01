<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;

/**
 * Behat task
 */
class Behat extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'behat';
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

        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('behat');
        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--format=%s', $config['format']);
        $arguments->addOptionalArgument('--suite=%s', $config['suite']);
        $arguments->addOptionalArgument('--stop_on_failure', $config['stop_on_failure']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
