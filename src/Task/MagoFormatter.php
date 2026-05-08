<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MagoFormatter extends Mago
{

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'type' => 'default',
        ]);

        $resolver->addAllowedTypes('type', ['string']);
        $resolver->addAllowedValues('type', ['default', 'dry-run', 'check', 'staged']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();
        $arguments = $this->processBuilder->createArgumentsForCommand('mago');
        $arguments->add('format');

        $arguments->addOptionalArgument('--dry-run', 'dry-run' === $config['type']);
        $arguments->addOptionalArgument('--check', 'check' === $config['type']);
        $arguments->addOptionalArgument('--staged', 'staged' === $config['type']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
