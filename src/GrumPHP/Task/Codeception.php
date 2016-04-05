<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'config_file' => null,
            'suite' => null,
            'test'  => null,
            'fail-fast' => false
        ));

        $resolver->addAllowedTypes('config_file', array('null', 'string'));
        $resolver->addAllowedTypes('suite', array('null', 'string'));
        $resolver->addAllowedTypes('test', array('null', 'string'));
        $resolver->addAllowedTypes('fail-fast', array('bool'));

        return $resolver;
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
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('codecept');
        $arguments->add('run');
        $arguments->addOptionalArgument('--config=%s', $config['config_file']);
        $arguments->addOptionalArgument('--fail-fast', $config['fail-fast']);
        $arguments->addOptionalArgument('%s', $config['suite']);
        $arguments->addOptionalArgument('%s', $config['test']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $process->getOutput());
        }

        return TaskResult::createPassed($this, $context);
    }
}
