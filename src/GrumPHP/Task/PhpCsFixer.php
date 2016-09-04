<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Php-cs-fixer task
 */
class PhpCsFixer extends AbstractPhpCsFixerTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phpcsfixer';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'config' => null,
            'config_file' => null,
            'fixers' => array(),
            'level' => null,
            'verbose' => true,
        ));

        $resolver->addAllowedTypes('config', array('null', 'string'));
        $resolver->addAllowedTypes('config_file', array('null', 'string'));
        $resolver->addAllowedTypes('fixers', array('array'));
        $resolver->addAllowedTypes('level', array('null', 'string'));
        $resolver->addAllowedTypes('verbose', array('bool'));

        return $resolver;
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
        $this->formatter->resetCounter();

        $arguments = $this->processBuilder->createArgumentsForCommand('php-cs-fixer');
        $arguments->add('--format=json');
        $arguments->add('--dry-run');
        $arguments->addOptionalArgument('--level=%s', $config['level']);
        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--config-file=%s', $config['config_file']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);
        $arguments->addOptionalCommaSeparatedArgument('--fixers=%s', $config['fixers']);
        $arguments->add('fix');

        if ($context instanceof RunContext && $config['config_file'] !== null) {
            return $this->runOnAllFiles($context, $arguments);
        }

        return $this->runOnChangedFiles($context, $arguments, $files);
    }
}
