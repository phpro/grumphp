<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * SecurityChecker task
 */
class SecurityChecker extends AbstractExternalTask
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'securitychecker';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'lockfile' => './composer.lock',
            'format' => null,
            'end_point' => null,
            'timeout' => null,
            'run_always' => false,
        ));

        $resolver->addAllowedTypes('lockfile', array('string'));
        $resolver->addAllowedTypes('format', array('null', 'string'));
        $resolver->addAllowedTypes('end_point', array('null', 'string'));
        $resolver->addAllowedTypes('timeout', array('null', 'int'));
        $resolver->addAllowedTypes('run_always', array('bool'));

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
            ->path(pathinfo($config['lockfile'], PATHINFO_DIRNAME))
            ->name(pathinfo($config['lockfile'], PATHINFO_BASENAME));
        if (0 === count($files) && !$config['run_always']) {
            return;
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('security-checker');
        $arguments->add('security:check');
        $arguments->addOptionalArgument('%s', $config['lockfile']);
        $arguments->addOptionalArgument('--format=%s', $config['format']);
        $arguments->addOptionalArgument('--end-point=%s', $config['end_point']);
        $arguments->addOptionalArgument('--timeout=%s', $config['timeout']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
