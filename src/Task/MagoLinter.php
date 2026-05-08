<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagoLinter extends Mago
{

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'semantics' => null,
            'pedantic' => null,
            'only' => [],
            'staged' => null,
        ]);

        $resolver->addAllowedTypes('semantics', ['null', 'bool']);
        $resolver->addAllowedTypes('pedantic', ['null', 'bool']);
        $resolver->addAllowedTypes('only', ['array']);
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
        $arguments->add('lint');

        $this->addFixArguments($arguments, $config, $fix);
        $this->addSharedArguments($arguments, $config);

        $arguments->addOptionalCommaSeparatedArgument('--only=%s', $config['only']);
        $arguments->addOptionalArgument('--semantics', $config['semantics']);
        $arguments->addOptionalArgument('--pedantic', $config['pedantic']);
        $arguments->addOptionalArgument('--staged', $config['staged']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
