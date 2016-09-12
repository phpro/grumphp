<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PHP parallel lint task.
 */
class PHPLint extends AbstractExternalTask
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
        $resolver->setDefaults(array(
            'jobs' => null,
            'exclude' => array(),
            'triggered_by' => array('php'),
        ));

        $resolver->setAllowedTypes('jobs', array('int', 'null'));
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

        $args = $this->processBuilder->createArgumentsForCommand('parallel-lint');
        $args->add('--no-colors');
        if (!empty($config['jobs'])) {
            $args->add('-j');
            $args->add($config['jobs']);
        }
        $args->addArgumentArrayWithSeparatedValue('--exclude', $config['exclude']);
        $args->add('-e');
        $args->addOptionalCommaSeparatedArgument('%s', $config['triggered_by']);
        $args->addFiles($files);

        $process = $this->processBuilder->buildProcess($args);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
