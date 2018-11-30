<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecurityChecker extends AbstractExternalTask
{
    public function getName(): string
    {
        return 'securitychecker';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'lockfile' => './composer.lock',
            'format' => null,
            'end_point' => null,
            'timeout' => null,
            'run_always' => false,
        ]);

        $resolver->addAllowedTypes('lockfile', ['string']);
        $resolver->addAllowedTypes('format', ['null', 'string']);
        $resolver->addAllowedTypes('end_point', ['null', 'string']);
        $resolver->addAllowedTypes('timeout', ['null', 'int']);
        $resolver->addAllowedTypes('run_always', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();

        $files = $context->getFiles()
            ->path(pathinfo($config['lockfile'], PATHINFO_DIRNAME))
            ->name(pathinfo($config['lockfile'], PATHINFO_BASENAME));
        if (0 === \count($files) && !$config['run_always']) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('security-checker');
        $arguments->add('security:check');
        $arguments->addOptionalArgument('%s', $config['lockfile']);
        $arguments->addOptionalArgument('--format=%s', $config['format']);
        $arguments->addOptionalArgument('--end-point=%s', $config['end_point']);
        $arguments->addOptionalArgument('--timeout=%s', $config['timeout']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
