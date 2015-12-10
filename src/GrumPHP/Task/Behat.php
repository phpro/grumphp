<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'config' => null,
            'format' => null,
            'suite' => null,
            'stop_on_failure' => false,
        ));

        $resolver->addAllowedTypes('config', array('null', 'string'));
        $resolver->addAllowedTypes('format', array('null', 'string'));
        $resolver->addAllowedTypes('suite', array('null', 'string'));
        $resolver->addAllowedTypes('stop_on_failure', array('bool'));

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
