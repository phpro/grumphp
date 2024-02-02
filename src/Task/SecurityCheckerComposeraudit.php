<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
class SecurityCheckerComposeraudit extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'format' => null,
            'locked' => true,
            'no_dev' => false,
            'run_always' => false,
            'working_dir' => null,
        ]);

        $resolver->addAllowedTypes('format', ['null', 'string']);
        $resolver->addAllowedTypes('locked', ['bool']);
        $resolver->addAllowedTypes('no_dev', ['bool']);
        $resolver->addAllowedTypes('run_always', ['bool']);
        $resolver->addAllowedTypes('working_dir', ['null', 'string']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();
        $files = $context->getFiles()
            ->path(pathinfo("composer.lock", PATHINFO_DIRNAME))
            ->name(pathinfo("composer.lock", PATHINFO_BASENAME));
        if (0 === \count($files) && !$config['run_always']) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('composer');
        $arguments->add('audit');
        $arguments->addOptionalArgument('--format=%s', $config['format']);
        $arguments->addOptionalArgument('--locked', $config['locked']);
        $arguments->addOptionalArgument('--no-dev', $config['no_dev']);
        $arguments->addOptionalArgument('--working-dir=%s', $config['working_dir']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
