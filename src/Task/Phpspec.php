<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Phpspec extends AbstractExternalTask
{
    public function getName(): string
    {
        return 'phpspec';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config_file' => null,
            'format' => null,
            'stop_on_failure' => false,
            'verbose' => false,
        ]);

        $resolver->addAllowedTypes('config_file', ['null', 'string']);
        $resolver->addAllowedTypes('format', ['null', 'string']);
        $resolver->addAllowedTypes('stop_on_failure', ['bool']);
        $resolver->addAllowedTypes('verbose', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $files = $context->getFiles()->name('*.php');
        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('phpspec');
        $arguments->add('run');
        $arguments->add('--no-interaction');
        $arguments->addOptionalArgument('--config=%s', $config['config_file']);
        $arguments->addOptionalArgument('--format=%s', $config['format']);
        $arguments->addOptionalArgument('--stop-on-failure', $config['stop_on_failure']);
        $arguments->addOptionalArgument('--verbose', $config['verbose']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
