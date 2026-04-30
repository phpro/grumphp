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
class Mago extends AbstractExternalTask
{

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'formatter' => true,
            'formatter_options' => ['--staged'],
            'linter' => true,
            'linter_options' => ['--staged'],
            'analyzer' => true,
            'analyzer_options' => ['--staged'],
            'guard' => false,
            'guard_options' => [],
        ]);

        $resolver->addAllowedTypes('formatter', ['bool']);
        $resolver->addAllowedTypes('formatter_options', ['array']);
        $resolver->addAllowedTypes('linter', ['bool']);
        $resolver->addAllowedTypes('linter_options', ['array']);
        $resolver->addAllowedTypes('analyzer', ['bool']);
        $resolver->addAllowedTypes('analyzer_options', ['array']);
        $resolver->addAllowedTypes('guard', ['bool']);
        $resolver->addAllowedTypes('guard_options', ['array']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        if ($config['formatter'] === false && $config['linter'] === false && $config['analyzer'] === false && $config['guard'] === false) {
            return TaskResult::createSkipped($this, $context);
        }

        $commandMap = [
            'formatter' => 'fmt',
            'linter' => 'lint',
            'analyzer' => 'analyze',
            'guard' => 'guard',
        ];

        foreach ($commandMap as $configKey => $command) {
            if ($config[$configKey] !== true) {
                continue;
            }

            $arguments = $this->processBuilder->createArgumentsForCommand('mago');
            $arguments->add($command);

            $arguments->addArgumentArray('%s', $config[$configKey . '_options']);

            $process = $this->processBuilder->buildProcess($arguments);
            $process->run();

            if (!$process->isSuccessful()) {
                return TaskResult::createFailed($this, $context, $this->formatter->format($process));
            }
        }

        return TaskResult::createPassed($this, $context);
    }
}
