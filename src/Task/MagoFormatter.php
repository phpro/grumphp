<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Fixer\Provider\FixableProcessResultProvider;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

/**
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
class MagoFormatter extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        return ConfigOptionsResolver::fromOptionsResolver(new OptionsResolver());
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $files = $context->getFiles()->extensions(['php']);
        if ($context instanceof GitPreCommitContext && 0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('mago');
        $arguments->add('format');
        $arguments->add('--dry-run');

        // `mago format --staged` cannot be combined with `--dry-run`, so we scope the pre-commit
        // run to the staged files explicitly instead. In a run context Mago uses its mago.toml.
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
