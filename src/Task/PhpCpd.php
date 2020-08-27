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
 * PhpCpd task.
 */
class PhpCpd extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'directory' => ['.'],
            'exclude' => ['vendor'],
            'fuzzy' => false,
            'min_lines' => 5,
            'min_tokens' => 70,
            'triggered_by' => ['php'],
        ]);

        $resolver->addAllowedTypes('directory', ['array']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('fuzzy', ['bool']);
        $resolver->addAllowedTypes('min_lines', ['int']);
        $resolver->addAllowedTypes('min_tokens', ['int']);
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
        $config = $this->getConfig()->getOptions();
        $files = $context->getFiles()->extensions($config['triggered_by']);

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('phpcpd');
        $extensions = array_map(function (string $extension) {
            return sprintf('*.%s', $extension);
        }, $config['triggered_by']);

        $arguments->addArgumentArray('--exclude=%s', $config['exclude']);
        $arguments->addRequiredArgument('--min-lines=%u', (string) $config['min_lines']);
        $arguments->addRequiredArgument('--min-tokens=%u', (string) $config['min_tokens']);
        $arguments->addOptionalCommaSeparatedArgument('--suffix=%s', $extensions);
        $arguments->addOptionalArgument('--fuzzy', $config['fuzzy']);
        $arguments->addArgumentArray('%s', $config['directory']);

        $process = $this->processBuilder->buildProcess($arguments);

        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
