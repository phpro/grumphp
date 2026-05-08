<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
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
class Mago extends AbstractExternalTask
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
        return TaskResult::createFailed(
            $this,
            $context,
            'The mago task is split into 4 distinct tasks.'.PHP_EOL
            . 'Please use the following tasks instead:'.PHP_EOL.PHP_EOL
            . '- mago_analyze '
            . '(https://github.com/phpro/grumphp/blob/master/doc/tasks/mago/analyzer.md)'.PHP_EOL
            . '- mago_format '
            . '(https://github.com/phpro/grumphp/blob/master/doc/tasks/mago/formatter.md)'.PHP_EOL
            . '- mago_guard '
            . '(https://github.com/phpro/grumphp/blob/master/doc/tasks/mago/guard.md)'.PHP_EOL
            . '- mago_lint '
            . '(https://github.com/phpro/grumphp/blob/master/doc/tasks/mago/linter.md)'.PHP_EOL
        );
    }

    protected static function configureSharedOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'retain-codes' => [],
            'ignore-baseline' => null,
            'fix' => null,
            'fail-on-remaining' => null,
            'sort' => null,
            'fixable-only' => null,
            'reporting-format' => null,
            'reporting-target' => null,
            'minimum-report-level' => null,
            'minimum-fail-level' => null,
            'dry-run' => null,
        ]);

        $resolver->addAllowedTypes('retain-codes', ['array']);
        $resolver->addAllowedTypes('ignore-baseline', ['null', 'bool']);
        $resolver->addAllowedTypes('fix', ['null', 'string']);
        $resolver->addAllowedTypes('fail-on-remaining', ['null', 'bool']);
        $resolver->addAllowedTypes('sort', ['null', 'bool']);
        $resolver->addAllowedTypes('fixable-only', ['null', 'bool']);
        $resolver->addAllowedTypes('reporting-format', ['null', 'string']);
        $resolver->addAllowedTypes('reporting-target', ['null', 'string']);
        $resolver->addAllowedTypes('minimum-report-level', ['null', 'string']);
        $resolver->addAllowedTypes('minimum-fail-level', ['null', 'string']);
        $resolver->addAllowedTypes('dry-run', ['null', 'bool']);

        $resolver->addAllowedValues('fix', [null, 'safe', 'potentially-unsafe', 'unsafe']);
        $resolver->addAllowedValues('reporting-format', [
            null, 'rich', 'medium', 'short', 'ariadne', 'github', 'gitlab',
            'json', 'count', 'code-count', 'checkstyle', 'emacs', 'sarif',
        ]);
        $resolver->addAllowedValues('reporting-target', [null, 'stdout', 'stderr']);
        $resolver->addAllowedValues('minimum-report-level', [null, 'note', 'help', 'warning', 'error']);
        $resolver->addAllowedValues('minimum-fail-level', [null, 'note', 'help', 'warning', 'error']);
    }

    protected function resolveFixOption(array $config): ?string
    {
        return $config['fix'];
    }

    protected function validateFixCompatibility(
        array $config,
        ?string $fix,
        ContextInterface $context
    ): ?TaskResultInterface {
        $error = match (true) {
            null === $fix && $config['fail-on-remaining']
                => 'Fail on remaining option is only supported with fix option.',
            null === $fix && $config['dry-run']
                => 'Dry run option is only supported with fix option.',
            null !== $fix && $config['fixable-only']
                => 'Fixable-only option is not supported with fix option.',
            null !== $fix && $config['reporting-format']
                => 'Reporting format option is not supported with fix option.',
            null !== $fix && $config['reporting-target']
                => 'Reporting target option is not supported with fix option.',
            default => null,
        };

        return null !== $error ? TaskResult::createFailed($this, $context, $error) : null;
    }

    protected function addFixArguments(
        ProcessArgumentsCollection $arguments,
        array $config,
        ?string $fix
    ): void {
        if (null === $fix) {
            return;
        }

        $arguments->add('--fix');
        $arguments->addOptionalArgument('--potentially-unsafe', 'potentially-unsafe' === $fix);
        $arguments->addOptionalArgument('--unsafe', 'unsafe' === $fix);
        $arguments->addOptionalArgument('--fail-on-remaining', $config['fail-on-remaining']);
        $arguments->addOptionalArgument('--dry-run', $config['dry-run']);
    }

    protected function addSharedArguments(
        ProcessArgumentsCollection $arguments,
        array $config
    ): void {
        $arguments->addOptionalArgument('--fixable-only', $config['fixable-only']);
        $arguments->addOptionalArgumentWithSeparatedValue('--reporting-format', $config['reporting-format']);
        $arguments->addOptionalArgumentWithSeparatedValue('--reporting-target', $config['reporting-target']);
        $arguments->addArgumentArrayWithSeparatedValue('--retain-code', $config['retain-codes']);
        $arguments->addOptionalArgumentWithSeparatedValue('--minimum-report-level', $config['minimum-report-level']);
        $arguments->addOptionalArgumentWithSeparatedValue('--minimum-fail-level', $config['minimum-fail-level']);
        $arguments->addOptionalArgument('--ignore-baseline', $config['ignore-baseline']);
        $arguments->addOptionalArgument('--sort', $config['sort']);
    }
}
