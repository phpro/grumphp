<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Fixer\Provider\FixableProcessResultProvider;
use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

/**
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
abstract class Mago extends AbstractExternalTask
{

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    protected static function configureSharedOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'retain-codes' => [],
            'ignore-baseline' => null,
            'sort' => null,
            'fix-mode' => 'safe',
            'minimum-report-level' => null,
        ]);

        $resolver->addAllowedTypes('retain-codes', ['array']);
        $resolver->addAllowedTypes('ignore-baseline', ['null', 'bool']);
        $resolver->addAllowedTypes('sort', ['null', 'bool']);
        $resolver->addAllowedTypes('fix-mode', ['string']);
        $resolver->addAllowedTypes('minimum-report-level', ['null', 'string']);

        $resolver->addAllowedValues('fix-mode', ['safe', 'potentially-unsafe', 'unsafe']);
        $resolver->addAllowedValues('minimum-report-level', [null, 'note', 'help', 'warning', 'error']);
    }

    /**
     * @param array{
     *     retain-codes: array<array-key, string>,
     *     ignore-baseline: bool|null,
     *     sort: bool|null,
     *     minimum-report-level: string|null,
     * } $config
     */
    protected function addCommonArguments(
        ProcessArgumentsCollection $arguments,
        array $config
    ): void {
        $arguments->addArgumentArrayWithSeparatedValue('--retain-code', $config['retain-codes']);
        $arguments->addOptionalArgument('--ignore-baseline', $config['ignore-baseline']);
        $arguments->addOptionalArgument('--sort', $config['sort']);
        $arguments->addOptionalArgumentWithSeparatedValue('--minimum-report-level', $config['minimum-report-level']);
    }

    /**
     * @param array{
     *     retain-codes: array<array-key, string>,
     *     ignore-baseline: bool|null,
     *     sort: bool|null,
     *     fix-mode: string,
     *     minimum-report-level: string|null,
     * } $config
     */
    protected function addSharedArguments(
        ProcessArgumentsCollection $arguments,
        array $config
    ): void {
        $arguments->add('--fix');
        $arguments->add('--dry-run');
        $arguments->add('--fail-on-remaining');
        $this->addCommonArguments($arguments, $config);
    }

    /**
     * @param array{'fix-mode': string} $config
     */
    protected function createFailedWithFix(
        ContextInterface $context,
        ProcessArgumentsCollection $arguments,
        string $message,
        array $config
    ): TaskResultInterface {
        return FixableProcessResultProvider::provide(
            TaskResult::createFailed($this, $context, $message),
            function () use ($arguments, $config): Process {
                $arguments->removeElement('--dry-run');
                $arguments->removeElement('--fail-on-remaining');
                match ($config['fix-mode']) {
                    'potentially-unsafe' => $arguments->add('--potentially-unsafe'),
                    'unsafe' => $arguments->add('--unsafe'),
                    default => null,
                };
                return $this->processBuilder->buildProcess($arguments);
            }
        );
    }
}
