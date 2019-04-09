<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Paratest extends AbstractExternalTask
{
    public function getName(): string
    {
        return 'paratest';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'processes'      => null,
                'functional'     => false,
                'phpunit'        => null,
                'configuration'  => null,
                'always_execute' => false,
                'group'          => [],
                'runner'         => null,
                'debugger'       => null,
                'coverage-xml'   => null,
                'coverage-html'  => null,
                'log-junit'      => null,
                'testsuite'      => null,
                'config'         => null,
            ]
        );

        $resolver->addAllowedTypes('processes', ['null', 'integer', 'string']);
        $resolver->addAllowedTypes('functional', ['bool']);
        $resolver->addAllowedTypes('phpunit', ['null', 'string']);
        $resolver->addAllowedTypes('configuration', ['null', 'string']);
        $resolver->addAllowedTypes('always_execute', ['bool']);
        $resolver->addAllowedTypes('runner', ['null', 'string']);
        $resolver->addAllowedTypes('debugger', ['null', 'array']);
        $resolver->addAllowedTypes('coverage-xml', ['null', 'string']);
        $resolver->addAllowedTypes('coverage-html', ['null', 'string']);
        $resolver->addAllowedTypes('log-junit', ['null', 'string']);
        $resolver->addAllowedTypes('testsuite', ['null', 'string']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();

        $files = $context->getFiles()->name('*.php');
        if (!$config['always_execute'] && 0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('paratest');
        $arguments->addOptionalArgument('-p %s', $config['processes']);
        $arguments->addOptionalArgument('-f', $config['functional']);
        $arguments->addOptionalArgument('-c %s', $config['configuration']);
        $arguments->addOptionalArgument('--phpunit %s', $config['phpunit']);
        $arguments->addOptionalArgument('--runner %s', $config['runner']);
        $arguments->addOptionalArgument('--coverage-xml %s', $config['coverage-xml']);
        $arguments->addOptionalArgument('--coverage-html %s', $config['coverage-html']);
        $arguments->addOptionalArgument('--log-junit %s', $config['log-junit']);
        $arguments->addOptionalArgument('--testsuite %s', $config['testsuite']);
        $arguments->addOptionalCommaSeparatedArgument('--group=%s', $config['group']);

        $coverageEnabled = !empty($config['coverage-xml'])
            || !empty($config['coverage-html'])
            || !empty($config['log-junit']);

        if ($coverageEnabled && !empty($config['debugger'])) {
            $bin = $config['debugger']['bin'];
            $args = $config['debugger']['args'];
            $arguments = new ProcessArgumentsCollection(array_merge([$bin], $args, $arguments->toArray()));
        }

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
