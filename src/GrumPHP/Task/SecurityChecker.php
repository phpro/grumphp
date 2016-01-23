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
     * @return string
     */
    public function getName()
    {
        return 'securitychecker';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'lockfile' => null,
            'format' => null,
            'end_point' => null,
            'timeout' => null,
        ));

        $resolver->addAllowedTypes('lockfile', array('null', 'string'));
        $resolver->addAllowedTypes('format', array('null', 'string'));
        $resolver->addAllowedTypes('end_point', array('null', 'string'));
        $resolver->addAllowedTypes('timeout', array('null', 'int'));

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

        $arguments = $this->processBuilder->createArgumentsForCommand('security-checker');
        $arguments->add('security:check');
        if ($config['lockfile']) {
            $arguments->add($config['lockfile']);
        }
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
