<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Traits\FiltersFilesTrait;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhpLintParallel extends AbstractExternalParallelTask
{
    use FiltersFilesTrait;

    public function getName(): string
    {
        return 'phplint_parallel';
    }

    public function getExecutableName(): string
    {
        return 'parallel-lint';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'jobs' => null,
            'exclude' => [],
            'ignore_patterns' => [],
            'triggered_by' => ['php', 'phtml', 'php3', 'php4', 'php5'],
        ]);

        $resolver->setAllowedTypes('jobs', ['int', 'null']);
        $resolver->setAllowedTypes('exclude', 'array');
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->setAllowedTypes('triggered_by', 'array');

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    /**
     * {@inheritdoc}
     */
    public function hasWorkToDo(ContextInterface $context): bool
    {
        $config = $this->getConfiguration();
        $files  = $this->getFilteredFiles($config, $context);

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

        $arguments->add('--no-colors');
        $arguments->addOptionalArgumentWithSeparatedValue('-j', (string)$config['jobs']);
        $arguments->addArgumentArrayWithSeparatedValue('--exclude', $config['exclude']);
        $files = $this->getFilteredFiles($config, $context);
        $arguments->addFiles($files);

        return $arguments;
    }
}
