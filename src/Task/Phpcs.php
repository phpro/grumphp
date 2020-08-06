<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Fixer\Provider\FixableProcessResultProvider;
use GrumPHP\Formatter\PhpcsFormatter;
use GrumPHP\Process\TmpFileUsingProcessRunner;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class Phpcs extends AbstractExternalTask
{
    /**
     * @var PhpcsFormatter
     */
    protected $formatter;

    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'standard' => [],
            'tab_width' => null,
            'encoding' => null,
            'whitelist_patterns' => [],
            'ignore_patterns' => [],
            'sniffs' => [],
            'severity' => null,
            'error_severity' => null,
            'warning_severity' => null,
            'triggered_by' => ['php'],
            'report' => 'full',
            'report_width' => null,
            'exclude' => [],
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
        $resolver->addAllowedTypes('exclude', ['array']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        /** @var array $config */
        $config = $this->getConfig()->getOptions();

        $files = $context->getFiles()
            ->extensions($config['triggered_by'])
            ->paths($config['whitelist_patterns'] ?? [])
            ->notPaths($config['ignore_patterns'] ?? []);

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $process = TmpFileUsingProcessRunner::run(
            function (string $tmpFile) use ($config): Process {
                $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
                $arguments = $this->addArgumentsFromConfig($arguments, $config);
                $arguments->add('--report-json');
                $arguments->add('--file-list='.$tmpFile);

                return $this->processBuilder->buildProcess($arguments);
            },
            static function () use ($files): \Generator {
                yield $files->toFileList();
            }
        );

        if (!$process->isSuccessful()) {
            $failedResult = TaskResult::createFailed($this, $context, $this->formatter->format($process));

            try {
                $fixerProcess = $this->createFixerProcess($this->formatter->getSuggestedFiles());
            } catch (CommandNotFoundException $e) {
                return $failedResult->withAppendedMessage(
                    PHP_EOL.'Info: phpcbf could not be found. Please consider to install it for auto-fixing.'
                );
            }

            if ($fixerProcess) {
                return FixableProcessResultProvider::provide(
                    $failedResult,
                    function () use ($fixerProcess): Process {
                        return $fixerProcess;
                    },
                    [0, 1]
                );
            }

            return $failedResult;
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param array<int, string> $suggestedFiles
     */
    private function createFixerProcess(array $suggestedFiles): ?Process
    {
        if (!$suggestedFiles) {
            return null;
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('phpcbf');
        $arguments = $this->addArgumentsFromConfig($arguments, $this->config->getOptions());
        $arguments->addArgumentArray('%s', $suggestedFiles);

        return $this->processBuilder->buildProcess($arguments);
    }

    private function addArgumentsFromConfig(
        ProcessArgumentsCollection $arguments,
        array $config
    ): ProcessArgumentsCollection {
        $arguments->addOptionalCommaSeparatedArgument('--standard=%s', (array) $config['standard']);
        $arguments->addOptionalCommaSeparatedArgument('--extensions=%s', (array) $config['triggered_by']);
        $arguments->addOptionalArgument('--tab-width=%s', $config['tab_width']);
        $arguments->addOptionalArgument('--encoding=%s', $config['encoding']);
        $arguments->addOptionalArgument('--report=%s', $config['report']);
        $arguments->addOptionalIntegerArgument('--report-width=%s', $config['report_width']);
        $arguments->addOptionalIntegerArgument('--severity=%s', $config['severity']);
        $arguments->addOptionalIntegerArgument('--error-severity=%s', $config['error_severity']);
        $arguments->addOptionalIntegerArgument('--warning-severity=%s', $config['warning_severity']);
        $arguments->addOptionalCommaSeparatedArgument('--sniffs=%s', $config['sniffs']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore=%s', $config['ignore_patterns']);
        $arguments->addOptionalCommaSeparatedArgument('--exclude=%s', $config['exclude']);

        return $arguments;
    }
}
