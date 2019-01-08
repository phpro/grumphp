<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Paratest task.
 */
class Paratest extends AbstractExternalParallelTask
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'paratest';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'runner'        => null,
            'debugger'      => null,
            'coverage-xml'  => null,
            'coverage-html' => null,
            'log-junit'     => null,
            'testsuite'     => null,
            'config'        => null,
            'processes'     => null,
        ]);

        $resolver->addAllowedTypes('runner', ['null', 'string']);
        $resolver->addAllowedTypes('debugger', ['null', 'array']);
        $resolver->addAllowedTypes('coverage-xml', ['null', 'string']);
        $resolver->addAllowedTypes('coverage-html', ['null', 'string']);
        $resolver->addAllowedTypes('log-junit', ['null', 'string']);
        $resolver->addAllowedTypes('testsuite', ['null', 'string']);
        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('processes', ['null', 'int']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * @param string $command
     * @param  array $config
     * @param ContextInterface $context
     * @return ProcessArgumentsCollection
     */
    protected function buildArguments(
        string $command,
        array $config,
        ContextInterface $context
    ): ProcessArgumentsCollection {
        $arguments = $this->processBuilder->createArgumentsForCommand($command);
        $arguments->addOptionalArgumentWithSeparatedValue('--runner', $config['runner']);
        $arguments->addOptionalArgumentWithSeparatedValue('--coverage-xml', $config['coverage-xml']);
        $arguments->addOptionalArgumentWithSeparatedValue('--coverage-html', $config['coverage-html']);
        $arguments->addOptionalArgumentWithSeparatedValue('--log-junit', $config['log-junit']);
        $arguments->addOptionalArgumentWithSeparatedValue('--testsuite', $config['testsuite']);
        $arguments->addOptionalArgumentWithSeparatedValue('-c', $config['config']);
        $arguments->addOptionalArgumentWithSeparatedValue('-p', (string) $config['processes']);

        $coverageEnabled = function ($config) {
            return !empty($config['coverage-xml'])
                || !empty($config['coverage-html'])
                || !empty($config['log-junit']);
        };
        if ($coverageEnabled($config) && !empty($config['debugger'])) {
            $bin       = $config['debugger']['bin'];
            $args      = $config['debugger']['args'];
            $arguments = new ProcessArgumentsCollection(array_merge([$bin], $args, $arguments->toArray()));
        }
        return $arguments;
    }
}
