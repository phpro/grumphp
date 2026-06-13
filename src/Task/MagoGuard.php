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
            'structural' => null,
            'perimeter' => null,
        ]);

        $resolver->addAllowedTypes('no-stubs', ['null', 'bool']);
        $resolver->addAllowedTypes('structural', ['null', 'bool']);
        $resolver->addAllowedTypes('perimeter', ['null', 'bool']);

        self::configureSharedOptions($resolver);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $arguments = $this->processBuilder->createArgumentsForCommand('mago');
        $arguments->add('guard');

        $this->addSharedArguments($arguments, $config);

        $arguments->addOptionalArgument('--no-stubs', $config['no-stubs']);
        $arguments->addOptionalArgument('--structural', $config['structural']);
        $arguments->addOptionalArgument('--perimeter', $config['perimeter']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return $this->createFailedWithFix($context, $arguments, $this->formatter->format($process), $config);
        }

        return TaskResult::createPassed($this, $context);
    }
}
