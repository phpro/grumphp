<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PHP parallel lint task.
 */
class PhpLint extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phplint';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'jobs' => null,
            'exclude' => [],
            'triggered_by' => ['php', 'phtml', 'php3', 'php4', 'php5'],
        ]);

        $resolver->setAllowedTypes('jobs', ['int', 'null']);
        $resolver->setAllowedTypes('exclude', 'array');
        $resolver->setAllowedTypes('triggered_by', 'array');

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();
        $files  = $context->getFiles()->extensions($config['triggered_by']);

        $arguments = $this->processBuilder->createArgumentsForCommand('parallel-lint');
        $arguments->add('--no-colors');
        $arguments->addOptionalArgumentWithSeparatedValue('-j', $config['jobs']);
        $arguments->addArgumentArrayWithSeparatedValue('--exclude', $config['exclude']);
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
