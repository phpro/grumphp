<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Atoum task
 */
class Atoum extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'atoum';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'config_file' => null,
            'bootstrap_file' => null,
            'directories' => array(),
            'files' => array(),
            'namespaces' => array(),
            'methods' => array(),
            'tags' => array(),
        ));

        $resolver->addAllowedTypes('config_file', array('null', 'string'));
        $resolver->addAllowedTypes('bootstrap_file', array('null', 'string'));
        $resolver->addAllowedTypes('directories', array('array'));
        $resolver->addAllowedTypes('files', array('array'));
        $resolver->addAllowedTypes('namespaces', array('array'));
        $resolver->addAllowedTypes('methods', array('array'));
        $resolver->addAllowedTypes('tags', array('array'));

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

        $arguments = $this->processBuilder->createArgumentsForCommand('atoum');
        $arguments->addOptionalArgumentWithSeparatedValue('-c', $config['config_file']);
        $arguments->addOptionalArgumentWithSeparatedValue('--bootstrap-file', $config['bootstrap_file']);
        $arguments->addSeparatedArgumentArray('--directories', $config['directories']);
        $arguments->addSeparatedArgumentArray('--files', $config['files']);
        $arguments->addSeparatedArgumentArray('--namespaces', $config['namespaces']);
        $arguments->addSeparatedArgumentArray('--methods', $config['methods']);
        $arguments->addSeparatedArgumentArray('--tags', $config['tags']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
