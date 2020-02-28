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
 * Atoum task.
 */
class Atoum extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config_file' => null,
            'bootstrap_file' => null,
            'directories' => [],
            'files' => [],
            'namespaces' => [],
            'methods' => [],
            'tags' => [],
        ]);

        $resolver->addAllowedTypes('config_file', ['null', 'string']);
        $resolver->addAllowedTypes('bootstrap_file', ['null', 'string']);
        $resolver->addAllowedTypes('directories', ['array']);
        $resolver->addAllowedTypes('files', ['array']);
        $resolver->addAllowedTypes('namespaces', ['array']);
        $resolver->addAllowedTypes('methods', ['array']);
        $resolver->addAllowedTypes('tags', ['array']);

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
        $files = $context->getFiles()->name('*.php');
        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfig()->getOptions();

        $arguments = $this->processBuilder->createArgumentsForCommand('atoum');
        $arguments->addOptionalArgumentWithSeparatedValue('-c', $config['config_file']);
        $arguments->addOptionalArgumentWithSeparatedValue('--bootstrap-file', $config['bootstrap_file']);
        $arguments->addSeparatedArgumentArray('--directories', $config['directories']);
        $arguments->addSeparatedArgumentArray('--files', $config['files']);
        $arguments->addSeparatedArgumentArray('--namespaces', $config['namespaces']);
        $arguments->addSeparatedArgumentArray('--methods', $config['methods']);
        $arguments->addSeparatedArgumentArray('--tags', $config['tags']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
