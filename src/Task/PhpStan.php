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
class PhpStan extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'autoload_file' => null,
            'configuration' => null,
            'level' => null,
            'ignore_patterns' => [],
            'force_patterns' => [],
            'triggered_by' => ['php'],
            'memory_limit' => null,
            'use_grumphp_paths' => true,
        ]);

        $resolver->addAllowedTypes('autoload_file', ['null', 'string']);
        $resolver->addAllowedTypes('configuration', ['null', 'string']);
        $resolver->addAllowedTypes('memory_limit', ['null', 'string']);
        $resolver->setAllowedValues(
            'level',
            /**
             * @param null|string|int $value
             */
            function ($value) {
                if ($value === null || $value === 'max') {
                    return true;
                }
                return false !== filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            }
        );
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->addAllowedTypes('force_patterns', ['array']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('use_grumphp_paths', ['bool']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
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

        $files = $context
            ->getFiles()
            ->notPaths($config['ignore_patterns'])
            ->extensions($config['triggered_by']);

        if (!empty($config['force_patterns'])) {
            $forcedFiles = $context->getFiles()->paths($config['force_patterns']);
            $files = $files->ensureFiles($forcedFiles);
        }

        if (0 === \count($files) && $config['use_grumphp_paths']) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('phpstan');

        $arguments->add('analyse');
        $arguments->addOptionalArgument('--autoload-file=%s', $config['autoload_file']);
        $arguments->addOptionalArgument('--configuration=%s', $config['configuration']);
        $arguments->addOptionalArgument('--memory-limit=%s', $config['memory_limit']);
        $arguments->addOptionalMixedArgument('--level=%s', $config['level']);
        $arguments->add('--no-ansi');
        $arguments->add('--no-interaction');
        $arguments->add('--no-progress');

        if ($config['use_grumphp_paths']) {
            $arguments->addFiles($files);
        }

        $process = $this->processBuilder->buildProcess($arguments);

        $process->run();

        if (!$process->isSuccessful()) {
            $failedResult = TaskResult::createFailed($this, $context, $this->formatter->format($process));

            if (!$this->hasFixableErrors($process)) {
                return $failedResult;
            }

            return FixableProcessResultProvider::provide(
                $failedResult,
                function () use ($arguments): Process {
                    $arguments->add('--fix');

                    return $this->processBuilder->buildProcess($arguments);
                }
            );
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * PHPStan (2.3.5+) ends its report with "Run PHPStan again with --fix" when at least one
     * reported error can be fixed automatically. Without fixable errors, "phpstan analyse --fix"
     * fails with "No fixable errors found", so the fixer is only offered when PHPStan suggests it.
     * The hint for ignored errors ("--fix-ignored") is not taken into account.
     */
    private function hasFixableErrors(Process $process): bool
    {
        return 1 === preg_match(
            '/Run PHPStan again with --fix(?![\w-])/',
            $process->getOutput().PHP_EOL.$process->getErrorOutput()
        );
    }
}
