<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PhpStan task.
 */
class PhpStan extends AbstractExternalTask
{
    public function getName(): string
    {
        return 'phpstan';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'autoload_file' => null,
            'configuration' => null,
            'level' => 0,
            'ignore_patterns' => [],
            'force_patterns' => [],
            'triggered_by' => ['php'],
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
    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();

        $files = $context
            ->getFiles()
            ->notPaths($config['ignore_patterns'])
            ->extensions($config['triggered_by']);

        if (!empty($config['force_patterns'])) {
            $forcedFiles = $context->getFiles()->paths($config['force_patterns']);
            $files = $files->ensureFiles($forcedFiles);
        }

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('phpstan');

        $arguments->add('analyse');
        $arguments->addOptionalArgument('--autoload-file=%s', $config['autoload_file']);
        $arguments->addOptionalArgument('--configuration=%s', $config['configuration']);
        $arguments->add(sprintf('--level=%u', $config['level']));
        $arguments->add('--no-ansi');
        $arguments->add('--no-interaction');
        $arguments->add('--no-progress');
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);

        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
