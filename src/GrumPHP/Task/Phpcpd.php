<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Phpcpd extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phpcpd';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'names' => array('*.php'),
            'names_exclude' => array(),
            'log_pmd' => '',
            'exclude'=> array('spec'),
            'min_lines' => 5,
            'min_tokens' => 70,
            'verbose' => false,
        ));

        $resolver->addAllowedTypes('names', array('string', 'array'));
        $resolver->addAllowedTypes('names_exclude', array('string', 'array'));
        $resolver->addAllowedTypes('log_pmd', array('string'));
        $resolver->addAllowedTypes('exclude', array('array'));
        $resolver->addAllowedTypes('min_lines', array('int'));
        $resolver->addAllowedTypes('min_tokens', array('int'));
        $resolver->addAllowedTypes('verbose', array('bool'));

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

        $arguments = $this->processBuilder->createArgumentsForCommand('phpcpd');
        $arguments->addOptionalCommaSeparatedArgument('--names=%s', $config['names']);
        $arguments->addOptionalCommaSeparatedArgument('--names-exclude=%s', $config['names_exclude']);
        $arguments->addOptionalArgument('--log-pmd=%s', $config['log_pmd']);
        $arguments->addOptionalArgument('--min-lines=%s', $config['min_lines']);
        $arguments->addOptionalArgument('--min-tokens=%s', $config['min_tokens']);
        $arguments->addOptionalCommaSeparatedArgument('--exclude=%s', $config['exclude']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
