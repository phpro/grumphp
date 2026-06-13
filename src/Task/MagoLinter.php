<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
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
        ]);

        $resolver->addAllowedTypes('semantics', ['null', 'bool']);
        $resolver->addAllowedTypes('pedantic', ['null', 'bool']);
        $resolver->addAllowedTypes('only', ['array']);

        self::configureSharedOptions($resolver);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $arguments = $this->processBuilder->createArgumentsForCommand('mago');
        $arguments->add('lint');

        $this->addSharedArguments($arguments, $config);

        $arguments->addOptionalCommaSeparatedArgument('--only=%s', $config['only']);
        $arguments->addOptionalArgument('--semantics', $config['semantics']);
        $arguments->addOptionalArgument('--pedantic', $config['pedantic']);
        $arguments->addOptionalArgument('--staged', $context instanceof GitPreCommitContext ?: null);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return $this->createFailedWithFix($context, $arguments, $this->formatter->format($process), $config);
        }

        return TaskResult::createPassed($this, $context);
    }
}
