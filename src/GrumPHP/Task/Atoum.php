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
            'directories' => null,
            'files' => null,
            'namespaces' => null,
            'methods' => null,
            'tags' => null,
        ));

        $resolver->addAllowedTypes('config_file', array('null', 'string'));
        $resolver->addAllowedTypes('bootstrap_file', array('null', 'string'));
        $resolver->addAllowedTypes('directories', array('null', 'string'));
        $resolver->addAllowedTypes('files', array('null', 'string'));
        $resolver->addAllowedTypes('namespaces', array('null', 'string'));
        $resolver->addAllowedTypes('methods', array('null', 'string'));
        $resolver->addAllowedTypes('tags', array('null', 'string'));

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
        if ($config['config_file']) {
            $arguments->addArgumentArrayWithSeparatedValue('-c', [$config['config_file']]);
        }
        if ($config['bootstrap_file']) {
            $arguments->addArgumentArrayWithSeparatedValue('--bootstrap-file', [$config['bootstrap_file']]);
        }
        if ($config['directories']) {
            $arguments->addArgumentArrayWithSeparatedValue('--directories', [$config['directories']]);
        }
        if ($config['files']) {
            $arguments->addArgumentArrayWithSeparatedValue('--files', [$config['files']]);
        }
        if ($config['namespaces']) {
            $arguments->addArgumentArrayWithSeparatedValue('--namespaces', [$config['namespaces']]);
        }
        if ($config['methods']) {
            $arguments->addArgumentArrayWithSeparatedValue('--methods', [$config['methods']]);
        }
        if ($config['tags']) {
            $arguments->addArgumentArrayWithSeparatedValue('--tags', [$config['tags']]);
        }

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
