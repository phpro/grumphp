<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Brunch task
 */
class Brunch extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'brunch';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'task' => 'build',
            'env' => 'production',
            'jobs' => 4,
            'debug' => false,
            'triggered_by' => ['js', 'jsx', 'coffee', 'ts', 'less', 'sass', 'scss']
        ]);

        $resolver->addAllowedTypes('task', ['string']);
        $resolver->addAllowedTypes('env', ['string']);
        $resolver->addAllowedTypes('jobs', ['int']);
        $resolver->addAllowedTypes('debug', ['bool']);
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
        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('brunch');
        $arguments->addRequiredArgument('%s', $config['task']);
        $arguments->addOptionalArgumentWithSeparatedValue('--env', $config['env']);
        $arguments->addOptionalArgumentWithSeparatedValue('--jobs', $config['jobs']);
        $arguments->addOptionalArgument('--debug', $config['debug']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
