<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Traits\FiltersFilesTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PhpStan task.
 */
class PhpStanParallel extends AbstractExternalParallelTask
{
    use FiltersFilesTrait;

    public function getName(): string
    {
        return 'phpstan_parallel';
    }

    public function getExecutableName(): string
    {
        return 'phpstan';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'autoload_file'   => null,
            'configuration'   => null,
            'level'           => 0,
            'ignore_patterns' => [],
            'force_patterns'  => [],
            'triggered_by'    => ['php'],
        ]);

        $resolver->addAllowedTypes('autoload_file', ['null', 'string']);
        $resolver->addAllowedTypes('configuration', ['null', 'string']);
        $resolver->addAllowedTypes('level', ['int']);
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->addAllowedTypes('force_patterns', ['array']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    /**
     * {@inheritdoc}
     */
    public function hasWorkToDo(ContextInterface $context): bool
    {
        $config               = $this->getConfiguration();
        $files                = $this->getFilteredFiles($config, $context);
        $hasMoreThanZeroFiles = \count($files) > 0;
        return $hasMoreThanZeroFiles;
    }

    /**
     * Override in Task
     *
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

        $files = $this->getFilteredFiles($config, $context);
        $arguments->add('analyse');
        $arguments->addOptionalArgument('--autoload-file=%s', $config['autoload_file']);
        $arguments->addOptionalArgument('--configuration=%s', $config['configuration']);
        $arguments->add(sprintf('--level=%u', $config['level']));
        $arguments->add('--no-ansi');
        $arguments->add('--no-interaction');
        $arguments->add('--no-progress');
        $arguments->addFiles($files);

        return $arguments;
    }
}
