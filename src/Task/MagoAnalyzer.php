<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagoAnalyzer extends Mago
{

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'no-stubs' => null,
            'staged' => null,
        ]);

        $resolver->addAllowedTypes('no-stubs', ['null', 'bool']);
        $resolver->addAllowedTypes('staged', ['null', 'bool']);

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
        $arguments->add('analyze');

        $this->addFixArguments($arguments, $config, $fix);
        $this->addSharedArguments($arguments, $config);

        $arguments->addOptionalArgument('--no-stubs', $config['no-stubs']);
        $arguments->addOptionalArgument('--staged', $config['staged']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
