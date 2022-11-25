<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Fixer\Provider\FixableProcessResultProvider;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class Stylelint extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            // Task config options
            'bin' => null,
            'triggered_by' => ['css', 'scss', 'sass', 'less', 'sss'],
            'whitelist_patterns' => [],

            // Stylelint native config options
            'config' => null,
            'config_basedir' => null,
            'ignore_path' => null,
            'ignore_pattern' => null,
            'syntax' => null,
            'custom_syntax' => null,
            'ignore_disables' => null,
            'disable_default_ignores' => null,
            'cache' => null,
            'cache_location' => null,
            'formatter' => null,
            'custom_formatter' => null,
            'quiet' => null,
            'color' => null,
            'report_needless_disables' => null,
            'report_invalid_scope_disables' => null,
            'report_descriptionless_disables' => null,
            'max_warnings' => null,
            'output_file' => null,
        ]);

        // Task config options
        $resolver->addAllowedTypes('bin', ['null', 'string']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('whitelist_patterns', ['array']);

        // Stylelint native config options
        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('config_basedir', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_path', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_pattern', ['null', 'string']);
        $resolver->addAllowedTypes('syntax', ['null', 'string']);
        $resolver->addAllowedTypes('custom_syntax', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_disables', ['null', 'bool']);
        $resolver->addAllowedTypes('disable_default_ignores', ['null', 'bool']);
        $resolver->addAllowedTypes('cache', ['null', 'bool']);
        $resolver->addAllowedTypes('cache_location', ['null', 'string']);
        $resolver->addAllowedTypes('formatter', ['null', 'string']);
        $resolver->addAllowedTypes('custom_formatter', ['null', 'string']);
        $resolver->addAllowedTypes('quiet', ['null', 'bool']);
        $resolver->addAllowedTypes('color', ['null', 'bool']);
        $resolver->addAllowedTypes('report_needless_disables', ['null', 'bool']);
        $resolver->addAllowedTypes('report_invalid_scope_disables', ['null', 'bool']);
        $resolver->addAllowedTypes('report_descriptionless_disables', ['null', 'bool']);
        $resolver->addAllowedTypes('max_warnings', ['null', 'integer']);
        $resolver->addAllowedTypes('output_file', ['null', 'string']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context
            ->getFiles()
            ->paths($config['whitelist_patterns'])
            ->extensions($config['triggered_by']);

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = isset($config['bin'])
            ? ProcessArgumentsCollection::forExecutable($config['bin'])
            : $this->processBuilder->createArgumentsForCommand('stylelint');

        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--config-basedir=%s', $config['config_basedir']);
        $arguments->addOptionalArgument('--ignore-path=%s', $config['ignore_path']);
        $arguments->addOptionalArgument('--ignore-pattern=%s', $config['ignore_pattern']);
        $arguments->addOptionalArgument('--syntax=%s', $config['syntax']);
        $arguments->addOptionalArgument('--custom-syntax=%s', $config['custom_syntax']);
        $arguments->addOptionalArgument('--ignore-disables', $config['ignore_disables']);
        $arguments->addOptionalArgument('--disable-default-ignores', $config['disable_default_ignores']);
        $arguments->addOptionalArgument('--cache', $config['cache']);
        $arguments->addOptionalArgument('--cache-location=%s', $config['cache_location']);
        $arguments->addOptionalArgument('--formatter=%s', $config['formatter']);
        $arguments->addOptionalArgument('--custom-formatter=%s', $config['custom_formatter']);
        $arguments->addOptionalArgument('--quiet', $config['quiet']);
        $arguments->addOptionalBooleanArgument('--%s', $config['color'], 'color', 'no-color');
        $arguments->addOptionalArgument('--report-needless-disables', $config['report_needless_disables']);
        $arguments->addOptionalArgument('--report-invalid-scope-disables', $config['report_invalid_scope_disables']);
        $arguments->addOptionalArgument(
            '--report-descriptionless-disables',
            $config['report_descriptionless_disables']
        );
        $arguments->addOptionalIntegerArgument('--max-warnings=%d', $config['max_warnings']);
        $arguments->addOptionalArgument('--output-file=%s', $config['output_file']);
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return FixableProcessResultProvider::provide(
                TaskResult::createFailed($this, $context, $this->formatter->format($process)),
                function () use ($arguments): Process {
                    $arguments->add('--fix');
                    return $this->processBuilder->buildProcess($arguments);
                }
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
