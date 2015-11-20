<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;

class Codeception extends AbstractExternalTask
{
    const COMMAND_NAME = 'codecept';

    /**
     * {@inheritdoc}
     */
    public function getCommandLocation()
    {
        return $this->externalCommandLocator->locate(self::COMMAND_NAME);
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

        $arguments = ProcessArgumentsCollection::forExecutable($this->getCommandLocation());
        $arguments->add('run');
        $arguments->addOptionalArgument('--config=%s', $config['config_file']);
        $arguments->addOptionalArgument('--fail-fast=%s', $config['fail-fast']);
        $arguments->addOptionalArgument('suite', $config['suite']);
        $arguments->addOptionalArgument('test', $config['test']);


        $this->processBuilder->setArguments($arguments->getValues());
        $process = $this->processBuilder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
