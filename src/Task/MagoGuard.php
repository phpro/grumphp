<?php

declare(strict_types=1);

namespace GrumPHP\Task;

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
class MagoGuard extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'mode' => null,
            'no-stubs' => null,
            'retain-codes' => [],
            'ignore-baseline' => null,
            'sort' => null,
            'minimum-report-level' => null,
        ]);

        $resolver->addAllowedTypes('mode', ['null', 'string']);
        $resolver->addAllowedTypes('no-stubs', ['null', 'bool']);
        $resolver->addAllowedTypes('retain-codes', ['array']);
        $resolver->addAllowedTypes('ignore-baseline', ['null', 'bool']);
        $resolver->addAllowedTypes('sort', ['null', 'bool']);
        $resolver->addAllowedTypes('minimum-report-level', ['null', 'string']);

        $resolver->addAllowedValues('mode', [null, 'structural', 'perimeter']);
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
        $arguments->add('guard');
        match ($config['mode']) {
            'structural' => $arguments->add('--structural'),
            'perimeter' => $arguments->add('--perimeter'),
            default => null,
        };
        $arguments->addArgumentArrayWithSeparatedValue('--retain-code', $config['retain-codes']);
        $arguments->addOptionalArgument('--ignore-baseline', $config['ignore-baseline']);
        $arguments->addOptionalArgument('--sort', $config['sort']);
        $arguments->addOptionalArgumentWithSeparatedValue('--minimum-report-level', $config['minimum-report-level']);
        $arguments->addOptionalArgument('--no-stubs', $config['no-stubs']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
