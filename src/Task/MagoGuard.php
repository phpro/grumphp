<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagoGuard extends Mago
{

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'no-stubs' => null,
            'checks' => 'all',
        ]);

        $resolver->addAllowedTypes('no-stubs', ['null', 'bool']);
        $resolver->addAllowedTypes('checks', ['string']);
        $resolver->addAllowedValues('checks', ['all', 'structural', 'perimeter']);

        self::configureSharedOptions($resolver);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();
        $fix = $this->resolveFixOption($config);

        if ($error = $this->validateFixCompatibility($config, $fix, $context)) {
            return $error;
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('mago');
        $arguments->add('guard');

        $this->addFixArguments($arguments, $config, $fix);
        $this->addSharedArguments($arguments, $config);

        $arguments->addOptionalArgument('--no-stubs', $config['no-stubs']);
        $arguments->addOptionalArgument('--structural', 'structural' === $config['checks']);
        $arguments->addOptionalArgument('--perimeter', 'perimeter' === $config['checks']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
