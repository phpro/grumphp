<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Ecs task.
 */
class Ecs extends AbstractExternalTask
{
    public function getName(): string
    {
        return 'ecs';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'whitelist_patterns' => [],
            'clear-cache' => false,
            'no-progress-bar' => true,
            'config' => null,
            'level' => null,
            'triggered_by' => ['php'],
            'files_on_pre_commit' => false,
        ]);

        $resolver->addAllowedTypes('whitelist_patterns', ['array']);
        $resolver->addAllowedTypes('clear-cache', ['bool']);
        $resolver->addAllowedTypes('no-progress-bar', ['bool']);
        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('level', ['null', 'string']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('files_on_pre_commit', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();

        /** @var array $whitelistPatterns */
        $whitelistPatterns = $config['whitelist_patterns'];

        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 < \count($whitelistPatterns)) {
            $files = $files->paths($whitelistPatterns);
        }

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('ecs');
        $arguments->add('check');

        $this->handleContextArguments(
            $arguments,
            $files,
            $whitelistPatterns,
            $config['files_on_pre_commit'] && $context instanceof GitPreCommitContext
        );

        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--level=%s', $config['level']);
        $arguments->addOptionalArgument('--clear-cache', $config['clear-cache']);
        $arguments->addOptionalArgument('--no-progress-bar', $config['no-progress-bar']);
        $arguments->addOptionalArgument('--ansi', true);
        $arguments->addOptionalArgument('--no-interaction', true);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param ProcessArgumentsCollection $arguments
     * @param FilesCollection            $files
     * @param array                      $whitelistPatterns
     * @param bool                       $addAsFilesToArguments
     */
    private function handleContextArguments(
        ProcessArgumentsCollection $arguments,
        FilesCollection $files,
        array $whitelistPatterns,
        bool $addAsFilesToArguments
    ): void {
        if ($addAsFilesToArguments) {
            $arguments->addFiles($files);

            return;
        }

        foreach ($whitelistPatterns as $whitelistPattern) {
            $arguments->add($whitelistPattern);
        }
    }
}
