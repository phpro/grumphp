<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecurityCheckerParallel extends AbstractExternalParallelTask
{
    public function getName(): string
    {
        return 'securitychecker_parallel';
    }

    public function getExecutableName(): string
    {
        return 'security-checker';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'lockfile'   => './composer.lock',
            'format'     => null,
            'end_point'  => null,
            'timeout'    => null,
            'run_always' => false,
        ]);

        $resolver->addAllowedTypes('lockfile', ['string']);
        $resolver->addAllowedTypes('format', ['null', 'string']);
        $resolver->addAllowedTypes('end_point', ['null', 'string']);
        $resolver->addAllowedTypes('timeout', ['null', 'int']);
        $resolver->addAllowedTypes('run_always', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    protected function hasWorkToDo(ContextInterface $context): bool
    {
        $config = $this->getConfiguration();

        $files = $context->getFiles()
            ->path(pathinfo($config['lockfile'], PATHINFO_DIRNAME))
            ->name(pathinfo($config['lockfile'], PATHINFO_BASENAME))
        ;
        if (0 === \count($files) && !$config['run_always']) {
            return true;
        }
        return false;
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
        $arguments->add('security:check');
        $arguments->addOptionalArgument('%s', $config['lockfile']);
        $arguments->addOptionalArgument('--format=%s', $config['format']);
        $arguments->addOptionalArgument('--end-point=%s', $config['end_point']);
        $arguments->addOptionalArgument('--timeout=%s', $config['timeout']);

        return $arguments;
    }
}
