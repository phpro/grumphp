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
 * PhpCpd task.
 */
class PhpCpdParallel extends AbstractExternalParallelTask
{
    use FiltersFilesTrait;

    public function getName(): string
    {
        return 'phpcpd_parallel';
    }

    public function getExecutableName(): string
    {
        return 'phpcpd';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'directory'       => '.',
            'exclude'         => ['vendor'],
            'names_exclude'   => [],
            'regexps_exclude' => [],
            'fuzzy'           => false,
            'min_lines'       => 5,
            'min_tokens'      => 70,
            'triggered_by'    => ['php'],
        ]);

        $resolver->addAllowedTypes('directory', ['string']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('names_exclude', ['array']);
        $resolver->addAllowedTypes('regexps_exclude', ['array']);
        $resolver->addAllowedTypes('fuzzy', ['bool']);
        $resolver->addAllowedTypes('min_lines', ['int']);
        $resolver->addAllowedTypes('min_tokens', ['int']);
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
    protected function hasWorkToDo(ContextInterface $context): bool
    {
        $config               = $this->getConfiguration();
        $files                = $this->getFilteredFiles($config, $context);
        $hasMoreThanZeroFiles = \count($files) > 0;
        return $hasMoreThanZeroFiles;
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
        $arguments  = $this->processBuilder->createArgumentsForCommand('phpcpd');
        $extensions = array_map(function ($extension) {
            return sprintf('*.%s', $extension);
        }, $config['triggered_by']);

        $arguments->addArgumentArray('--exclude=%s', $config['exclude']);
        $arguments->addArgumentArray('--names-exclude=%s', $config['names_exclude']);
        $arguments->addOptionalCommaSeparatedArgument('--regexps-exclude=%s', $config['regexps_exclude']);
        $arguments->addRequiredArgument('--min-lines=%u', (string) $config['min_lines']);
        $arguments->addRequiredArgument('--min-tokens=%u', (string) $config['min_tokens']);
        $arguments->addOptionalCommaSeparatedArgument('--names=%s', $extensions);
        $arguments->addOptionalArgument('--fuzzy', $config['fuzzy']);
        $arguments->add($config['directory']);

        return $arguments;
    }
}
