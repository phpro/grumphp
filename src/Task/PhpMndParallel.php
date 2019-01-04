<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Traits\FiltersFilesTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PhpMnd task
 */
class PhpMndParallel extends AbstractExternalParallelTask
{
    use FiltersFilesTrait;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'phpmnd_parallel';
    }

    /**
     * @return string
     */
    public function getExecutableName(): string
    {
        return 'phpmnd';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'directory'          => '.',
            'whitelist_patterns' => [],
            'exclude'            => [],
            'exclude_name'       => [],
            'exclude_path'       => [],
            'extensions'         => [],
            'hint'               => false,
            'ignore_numbers'     => [],
            'ignore_strings'     => [],
            'strings'            => false,
            'triggered_by'       => ['php'],
            'ignore_funcs'       => [],
        ]);
        $resolver->addAllowedTypes('directory', ['string']);
        $resolver->addAllowedTypes('whitelist_patterns', ['array']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('exclude_name', ['array']);
        $resolver->addAllowedTypes('exclude_path', ['array']);
        $resolver->addAllowedTypes('extensions', ['array']);
        $resolver->addAllowedTypes('hint', ['bool']);
        $resolver->addAllowedTypes('ignore_numbers', ['array']);
        $resolver->addAllowedTypes('ignore_strings', ['array']);
        $resolver->addAllowedTypes('strings', ['bool']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('ignore_funcs', ['array']);
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
        $arguments = $this->processBuilder->createArgumentsForCommand($command);
        $arguments->addArgumentArray('--exclude=%s', $config['exclude']);
        $arguments->addArgumentArray('--exclude-file=%s', $config['exclude_name']);
        $arguments->addArgumentArray('--exclude-path=%s', $config['exclude_path']);
        $arguments->addOptionalCommaSeparatedArgument('--extensions=%s', $config['extensions']);
        $arguments->addOptionalArgument('--hint', $config['hint']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore-numbers=%s', $config['ignore_numbers']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore-strings=%s', $config['ignore_strings']);
        $arguments->addOptionalArgument('--strings', $config['strings']);
        $arguments->addOptionalCommaSeparatedArgument('--suffixes=%s', $config['triggered_by']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore-funcs=%s', $config['ignore_funcs']);
        $arguments->add('--non-zero-exit-on-violation');
        $arguments->add($config['directory']);

        return $arguments;
    }
}
