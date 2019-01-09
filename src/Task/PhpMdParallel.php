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
 * PhpMd task.
 */
class PhpMdParallel extends AbstractExternalParallelTask
{
    use FiltersFilesTrait;

    public function getName(): string
    {
        return 'phpmd_parallel';
    }

    public function getExecutableName(): string
    {
        return 'phpmd';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'whitelist_patterns' => [],
            'exclude'            => [],
            'ruleset'            => ['cleancode', 'codesize', 'naming'],
            'triggered_by'       => ['php'],
        ]);

        $resolver->addAllowedTypes('whitelist_patterns', ['array']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('ruleset', ['array']);
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
        $arguments = $this->processBuilder->createArgumentsForCommand('phpmd');
        $files     = $this->getFilteredFiles($config, $context);
        $arguments->addCommaSeparatedFiles($files);
        $arguments->add('text');
        $arguments->addOptionalCommaSeparatedArgument('%s', $config['ruleset']);
        $arguments->addOptionalArgument('--exclude', !empty($config['exclude']));
        $arguments->addOptionalCommaSeparatedArgument('%s', $config['exclude']);

        return $arguments;
    }
}
