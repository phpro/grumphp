<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Gulp task
 */
class Gulp extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'gulp';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'gulp_file' => null,
            'task' => null,
            'triggered_by' => array('js', 'jsx', 'coffee', 'ts', 'less', 'sass', 'scss')
        ));

        $resolver->addAllowedTypes('gulp_file', array('null', 'string'));
        $resolver->addAllowedTypes('task', array('null', 'string'));
        $resolver->addAllowedTypes('triggered_by', array('array'));

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

        $arguments = $this->processBuilder->createArgumentsForCommand('gulp');
        $arguments->addOptionalArgument('--gulpfile=%s', $config['gulp_file']);
        $arguments->addOptionalArgument('%s', $config['task']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $process->getOutput());
        }

        return TaskResult::createPassed($this, $context);
    }
}
