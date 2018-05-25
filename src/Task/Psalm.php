<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Psalm task
 */
class Psalm extends AbstractExternalTask
{
    const TASK_NAME = 'psalm';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::TASK_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config' => null,
            'ignore_patterns' => [],
            'no_cache' => false,
            'report' => null,
            'threads' => 1,
            'triggered_by' => ['php'],
        ]);
        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->addAllowedTypes('no_cache', ['bool']);
        $resolver->addAllowedTypes('report', ['null', 'string']);
        $resolver->addAllowedTypes('threads', ['int']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();

        $files = $context
            ->getFiles()
            ->extensions($config['triggered_by']);

        $files = $files->notPaths($config['ignore_patterns']);

        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand(self::TASK_NAME);
        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--report=%s', $config['report']);
        $arguments->addOptionalArgument('--no-cache', $config['no_cache']);
        $arguments->addOptionalArgument('--threads=%d', $config['threads']);
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);

        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
