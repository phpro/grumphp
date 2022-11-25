<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Fixer\Provider\FixableProcessResultProvider;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class Rector extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config' => null,
            'triggered_by' => ['php'],
            'ignore_patterns' => [],
            'clear_cache' => true,
            'no_diffs' => false,
        ]);

        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->addAllowedTypes('clear_cache', ['bool']);
        $resolver->addAllowedTypes('no_diffs', ['bool']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context
            ->getFiles()
            ->notPaths($config['ignore_patterns'])
            ->extensions($config['triggered_by']);

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('rector');
        $arguments->add('process');
        $arguments->add('--dry-run');
        $arguments->add('--ansi');
        $arguments->add('--no-progress-bar');

        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--clear-cache', $config['clear_cache']);
        $arguments->addOptionalArgument('--no-diffs', $config['no_diffs']);

        if ($context instanceof GitPreCommitContext) {
            $arguments->addFiles($files);
        }

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return FixableProcessResultProvider::provide(
                TaskResult::createFailed($this, $context, $this->formatter->format($process)),
                function () use ($arguments): Process {
                    $arguments->removeElement('--dry-run');

                    return $this->processBuilder->buildProcess($arguments);
                }
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
