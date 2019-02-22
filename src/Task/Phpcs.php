<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Phpcs extends AbstractExternalTask
{
    public function getName(): string
    {
        return 'phpcs';
    }

    public function getConfigurableOptions(): OptionsResolver
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

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        /** @var array $config */
        $config = $this->getConfiguration();
        /** @var array $whitelistPatterns */
        $whitelistPatterns = $config['whitelist_patterns'];
        /** @var array $extensions */
        $extensions = $config['triggered_by'];

        /** @var \GrumPHP\Collection\FilesCollection $files */
        $files = $context->getFiles();
        if (\count($whitelistPatterns)) {
            $files = $files->paths($whitelistPatterns);
        }
        $files = $files->extensions($extensions);

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('phpcs');
        $arguments = $this->addArgumentsFromConfig($arguments, $config);
        $arguments->add('--report-json');
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            $output = $this->formatter->format($process);
            try {
                $arguments = $this->processBuilder->createArgumentsForCommand('phpcbf');
                $arguments = $this->addArgumentsFromConfig($arguments, $config);
                $output .= $this->formatter->formatErrorMessage($arguments, $this->processBuilder);
            } catch (RuntimeException $exception) { // phpcbf could not get found.
                $output .= PHP_EOL.'Info: phpcbf could not get found. Please consider to install it for suggestions.';
            }

            return TaskResult::createFailed($this, $context, $output);
        }

        return TaskResult::createPassed($this, $context);
    }

    protected function addArgumentsFromConfig(
        ProcessArgumentsCollection $arguments,
        array $config
    ): ProcessArgumentsCollection {
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

        return $arguments;
    }
}
