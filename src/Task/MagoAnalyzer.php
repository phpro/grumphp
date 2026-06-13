<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Fixer\Provider\FixableProcessResultProvider;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class MagoAnalyzer extends Mago
{

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'no-stubs' => null,
        ]);

        $resolver->addAllowedTypes('no-stubs', ['null', 'bool']);

        self::configureSharedOptions($resolver);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $arguments = $this->processBuilder->createArgumentsForCommand('mago');
        $arguments->add('analyze');
        $this->addCommonArguments($arguments, $config);
        $arguments->addOptionalArgument('--no-stubs', $config['no-stubs']);
        $arguments->addOptionalArgument('--staged', $context instanceof GitPreCommitContext ?: null);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return FixableProcessResultProvider::provide(
                TaskResult::createFailed($this, $context, $this->formatter->format($process)),
                function () use ($config): Process {
                    $fixArguments = $this->processBuilder->createArgumentsForCommand('mago');
                    $fixArguments->add('analyze');
                    $fixArguments->add('--fix');
                    $this->addCommonArguments($fixArguments, $config);
                    $fixArguments->addOptionalArgument('--no-stubs', $config['no-stubs']);
                    match ($config['fix-mode']) {
                        'potentially-unsafe' => $fixArguments->add('--potentially-unsafe'),
                        'unsafe' => $fixArguments->add('--unsafe'),
                        default => null,
                    };
                    return $this->processBuilder->buildProcess($fixArguments);
                }
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
