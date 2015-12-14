<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Composer task
 *
 * @package GrumPHP\Task
 */
class Composer extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'composer';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'file' => './composer.json',
            'no_check_all' => false,
            'no_check_lock' => false,
            'no_check_publish'  => false,
            'with_dependencies' => false,
            'strict' => false
        ));

        $resolver->addAllowedTypes('file', array('string'));
        $resolver->addAllowedTypes('no_check_all', array('bool'));
        $resolver->addAllowedTypes('no_check_lock', array('bool'));
        $resolver->addAllowedTypes('no_check_publish', array('bool'));
        $resolver->addAllowedTypes('with_dependencies', array('bool'));
        $resolver->addAllowedTypes('strict', array('bool'));

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
        $files = $context->getFiles()
            ->path(pathinfo($config['file'], PATHINFO_DIRNAME))
            ->name(pathinfo($config['file'], PATHINFO_BASENAME));
        if (0 === count($files)) {
            return;
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('composer');
        $arguments->add('validate');

        $arguments->addOptionalArgument('--no-check-all', $config['no_check_all']);
        $arguments->addOptionalArgument('--no-check-lock', $config['no_check_lock']);
        $arguments->addOptionalArgument('--no-check-publish', $config['no_check_publish']);
        $arguments->addOptionalArgument('--with-dependencies', $config['with_dependencies']);
        $arguments->addOptionalArgument('--strict', $config['strict']);
        $arguments->addOptionalArgument('%s', $config['file']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
