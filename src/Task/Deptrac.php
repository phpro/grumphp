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
 * Deptrac task.
 */
class Deptrac extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'cache_file' => null,
            'depfile' => null,
            'formatter' => null,
            'output' => null,
        ]);

        $resolver->addAllowedTypes('cache_file', ['null', 'string']);
        $resolver->addAllowedTypes('depfile', ['null', 'string']);
        $resolver->addAllowedTypes('formatter', ['null', 'string']);
        $resolver->addAllowedTypes('output', ['null', 'string']);

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
        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('deptrac');
        $arguments->add('analyse');
        $arguments->addOptionalArgument('--formatter=%s', $config['formatter']);
        $arguments->addOptionalArgument('--output=%s', $config['output']);
        $arguments->addOptionalArgument('--cache-file=%s', $config['cache_file']);
        $arguments->addOptionalArgument('--config-file=%s', $config['depfile']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
