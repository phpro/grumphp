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
class MagoAnalyzer extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'no-stubs' => null,
            'retain-codes' => [],
            'ignore-baseline' => null,
            'sort' => null,
            'fix-mode' => 'safe',
            'minimum-report-level' => null,
        ]);

        $resolver->addAllowedTypes('no-stubs', ['null', 'bool']);
        $resolver->addAllowedTypes('retain-codes', ['array']);
        $resolver->addAllowedTypes('ignore-baseline', ['null', 'bool']);
        $resolver->addAllowedTypes('sort', ['null', 'bool']);
        $resolver->addAllowedTypes('fix-mode', ['string']);
        $resolver->addAllowedTypes('minimum-report-level', ['null', 'string']);

        $resolver->addAllowedValues('fix-mode', ['safe', 'potentially-unsafe', 'unsafe']);
        $resolver->addAllowedValues('minimum-report-level', [null, 'note', 'help', 'warning', 'error']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $arguments = $this->processBuilder->createArgumentsForCommand('mago');
        $arguments->add('analyze');
        $arguments->addArgumentArrayWithSeparatedValue('--retain-code', $config['retain-codes']);
        $arguments->addOptionalArgument('--ignore-baseline', $config['ignore-baseline']);
        $arguments->addOptionalArgument('--sort', $config['sort']);
        $arguments->addOptionalArgumentWithSeparatedValue('--minimum-report-level', $config['minimum-report-level']);
        $arguments->addOptionalArgument('--no-stubs', $config['no-stubs']);
        $arguments->addOptionalArgument('--staged', $context instanceof GitPreCommitContext);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return FixableProcessResultProvider::provide(
                TaskResult::createFailed($this, $context, $this->formatter->format($process)),
                function () use ($arguments, $config): Process {
                    // $arguments still holds --staged when running from a pre-commit context,
                    // so the fix is scoped to the same files that were analyzed.
                    $arguments->add('--fix');
                    match ($config['fix-mode']) {
                        'potentially-unsafe' => $arguments->add('--potentially-unsafe'),
                        'unsafe' => $arguments->add('--unsafe'),
                        default => null,
                    };

                    return $this->processBuilder->buildProcess($arguments);
                }
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
