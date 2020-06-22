<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Paratest extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
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
                'coverage-clover'  => null,
                'coverage-html'  => null,
                'coverage-php'  => null,
                'coverage-xml'   => null,
                'log-junit'      => null,
                'testsuite'      => null,
                'config'         => null,
                'verbose'        => false,
            ]
        );

        $resolver->addAllowedTypes('processes', ['null', 'integer', 'string']);
        $resolver->addAllowedTypes('functional', ['bool']);
        $resolver->addAllowedTypes('phpunit', ['null', 'string']);
        $resolver->addAllowedTypes('configuration', ['null', 'string']);
        $resolver->addAllowedTypes('always_execute', ['bool']);
        $resolver->addAllowedTypes('runner', ['null', 'string']);
        $resolver->addAllowedTypes('coverage-clover', ['null', 'string']);
        $resolver->addAllowedTypes('coverage-html', ['null', 'string']);
        $resolver->addAllowedTypes('coverage-php', ['null', 'string']);
        $resolver->addAllowedTypes('coverage-xml', ['null', 'string']);
        $resolver->addAllowedTypes('log-junit', ['null', 'string']);
        $resolver->addAllowedTypes('testsuite', ['null', 'string']);
        $resolver->addAllowedTypes('verbose', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context->getFiles()->name('*.php');
        if (!$config['always_execute'] && 0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('paratest');
        $arguments->addOptionalArgument('-p=%s', $config['processes']);
        $arguments->addOptionalArgument('-f', $config['functional']);
        $arguments->addOptionalArgument('-c=%s', $config['configuration']);
        $arguments->addOptionalArgument('--phpunit=%s', $config['phpunit']);
        $arguments->addOptionalArgument('--runner=%s', $config['runner']);
        $arguments->addOptionalArgument('--coverage-clover=%s', $config['coverage-clover']);
        $arguments->addOptionalArgument('--coverage-html=%s', $config['coverage-html']);
        $arguments->addOptionalArgument('--coverage-php=%s', $config['coverage-php']);
        $arguments->addOptionalArgument('--coverage-xml=%s', $config['coverage-xml']);
        $arguments->addOptionalArgument('--log-junit=%s', $config['log-junit']);
        $arguments->addOptionalArgument('--testsuite=%s', $config['testsuite']);
        $arguments->addOptionalArgument('--verbose=1', $config['verbose']);
        $arguments->addOptionalCommaSeparatedArgument('--group=%s', $config['group']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
