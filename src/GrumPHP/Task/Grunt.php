<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Grunt task
 */
class Grunt extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'grunt';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'grunt_file' => null,
            'task' => null,
            'triggered_by' => array('js', 'jsx', 'coffee', 'ts', 'less', 'sass', 'scss')
        ));

        $resolver->addAllowedTypes('grunt_file', array('null', 'string'));
        $resolver->addAllowedTypes('task', array('null', 'string'));
        $resolver->addAllowedTypes('triggered_by', array('array'));

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
        $config = $this->getConfiguration();

        $files = $context->getFiles()->name(sprintf('/\.(%s)$/i', implode('|', $config['triggered_by'])));
        if (0 === count($config['triggered_by']) || 0 === count($files)) {
            return;
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('grunt');
        $arguments->addOptionalArgument('--gruntfile=%s', $config['grunt_file']);
        $arguments->addOptionalArgument('%s', $config['task']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
