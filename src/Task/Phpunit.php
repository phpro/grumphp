<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Phpunit task
 */
class Phpunit extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phpunit';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config_file' => null,
            'testsuite' => null,
            'group' => [],
            'always_execute' => false,
        ]);

        $resolver->addAllowedTypes('config_file', ['null', 'string']);
        $resolver->addAllowedTypes('testsuite', ['null', 'string']);
        $resolver->addAllowedTypes('group', ['array']);
        $resolver->addAllowedTypes('always_execute', ['bool']);

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

        $files = $context->getFiles()->name('*.php');
        if (0 === count($files) && !$config['always_execute']) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('phpunit');
        $arguments->addOptionalArgument('--configuration=%s', $config['config_file']);
        $arguments->addOptionalArgument('--testsuite=%s', $config['testsuite']);
        $arguments->addOptionalCommaSeparatedArgument('--group=%s', $config['group']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
