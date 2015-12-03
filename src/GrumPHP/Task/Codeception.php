<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;

/**
 * Codeception task
 *
 * @package GrumPHP\Task
 */
class Codeception extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'codeception';
    }

    /**
     * Default command configuration
     *
     * @return array
     */
    public function getDefaultConfiguration()
    {
        return array(
            'config_file'   => null,
            'suite'         => null,
            'test'          => null,
            'fail-fast'     => null
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

        $arguments = $this->processBuilder->createArgumentsForCommand('codecept');
        $arguments->add('run');
        $arguments->addOptionalArgument('--config=%s', $config['config_file']);
        $arguments->addOptionalArgument('--fail-fast=%s', $config['fail-fast']);
        $arguments->addOptionalArgument('suite', $config['suite']);
        $arguments->addOptionalArgument('test', $config['test']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
