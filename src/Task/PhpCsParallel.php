<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Traits\FiltersFilesTrait;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

/**
 * Phpcs task.
 *
 * @property \GrumPHP\Formatter\PhpcsFormatter $formatter
 */
class PhpCsParallel extends AbstractExternalParallelTask
{
    use FiltersFilesTrait;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'phpcs_parallel';
    }

    /**
     * @return string
     */
    public function getExecutableName(): string
    {
        return 'phpcs';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'standard'           => [],
            'tab_width'          => null,
            'encoding'           => null,
            'whitelist_patterns' => [],
            'ignore_patterns'    => [],
            'sniffs'             => [],
            'severity'           => null,
            'error_severity'     => null,
            'warning_severity'   => null,
            'triggered_by'       => ['php'],
            'report'             => 'full',
            'report_width'       => null,
            'parallel'           => null,
            'show_sniffs'        => null,
        ]);

        $resolver->addAllowedTypes('standard', ['array', 'null', 'string']);
        $resolver->addAllowedTypes('tab_width', ['null', 'int']);
        $resolver->addAllowedTypes('encoding', ['null', 'string']);
        $resolver->addAllowedTypes('whitelist_patterns', ['array']);
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->addAllowedTypes('sniffs', ['array']);
        $resolver->addAllowedTypes('severity', ['null', 'int']);
        $resolver->addAllowedTypes('error_severity', ['null', 'int']);
        $resolver->addAllowedTypes('warning_severity', ['null', 'int']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('report', ['null', 'string']);
        $resolver->addAllowedTypes('report_width', ['null', 'int']);
        $resolver->addAllowedTypes('parallel', ['null', 'int']);
        $resolver->addAllowedTypes('show_sniffs', ['null', 'bool']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function hasWorkToDo(ContextInterface $context): bool
    {
        $config = $this->getConfiguration();
        $files  = $this->getFilteredFiles($config, $context);

        $hasMoreThanZeroFiles = \count($files) > 0;
        return $hasMoreThanZeroFiles;
    }

    /**
     * @param Process $process
     * @param ContextInterface $context
     * @return TaskResultInterface
     */
    public function getTaskResult(Process $process, ContextInterface $context): TaskResultInterface
    {
        if (!$process->isSuccessful()) {
            $output = $this->formatter->format($process);
            try {
                $config    = $this->getConfiguration();
                $arguments = $this->buildArguments("phpcbf", $config, $context);
                $output    .= $this->formatter->formatErrorMessage($arguments, $this->processBuilder);
            } catch (RuntimeException $exception) { // phpcbf could not get found.
                $output .= PHP_EOL.'Info: phpcbf could not get found. Please consider to install it for suggestions.';
            }
            return TaskResult::createFailed($this, $context, $output);
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param string $command
     * @param  array $config
     * @param ContextInterface $context
     * @return ProcessArgumentsCollection
     */
    protected function buildArguments(
        string $command,
        array $config,
        ContextInterface $context
    ): ProcessArgumentsCollection {
        $arguments = $this->processBuilder->createArgumentsForCommand($command);
        $arguments->addOptionalCommaSeparatedArgument('--standard=%s', (array) $config['standard']);
        $arguments->addOptionalArgument('--tab-width=%s', $config['tab_width']);
        $arguments->addOptionalArgument('--encoding=%s', $config['encoding']);
        $arguments->addOptionalArgument('--report=%s', $config['report']);
        $arguments->addOptionalIntegerArgument('--report-width=%s', $config['report_width']);
        $arguments->addOptionalIntegerArgument('--severity=%s', $config['severity']);
        $arguments->addOptionalIntegerArgument('--error-severity=%s', $config['error_severity']);
        $arguments->addOptionalIntegerArgument('--warning-severity=%s', $config['warning_severity']);
        $arguments->addOptionalCommaSeparatedArgument('--sniffs=%s', $config['sniffs']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore=%s', $config['ignore_patterns']);
        $arguments->addOptionalIntegerArgument('--parallel=%s', $config['parallel']);
        if ($config['show_sniffs']) {
            $arguments->add('-s');
        }

        $arguments->add('--report-json');

        $files = $this->getFilteredFiles($config, $context);
        $arguments->addFiles($files);

        return $arguments;
    }
}
