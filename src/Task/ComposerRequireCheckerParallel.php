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
 * ComposerRequireChecker task.
 */
class ComposerRequireCheckerParallel extends AbstractExternalParallelTask
{
    use FiltersFilesTrait;

    public function getName(): string
    {
        return 'composer_require_checker_parallel';
    }

    public function getExecutableName(): string
    {
        return 'composer-require-checker';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'composer_file'       => 'composer.json',
            'config_file'         => null,
            'ignore_parse_errors' => false,
            'triggered_by'        => ['composer.json', 'composer.lock', '*.php'],
        ]);

        $resolver->addAllowedTypes('composer_file', ['string']);
        $resolver->addAllowedTypes('config_file', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_parse_errors', ['bool']);
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

        $arguments->add('check');
        $arguments->addOptionalArgument('--config-file=%s', $config['config_file']);
        $arguments->addOptionalArgument('--ignore-parse-errors', $config['ignore_parse_errors']);
        $arguments->add('--no-interaction');
        $arguments->add($config['composer_file']);

        return $arguments;
    }
}
