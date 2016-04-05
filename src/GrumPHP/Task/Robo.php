<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Robo task
 */
class Robo extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'robo';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'load_from' => null,
            'task' => null,
            'triggered_by' => array('php')
        ));

        $resolver->addAllowedTypes('load_from', array('null', 'string'));
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

        $arguments = $this->processBuilder->createArgumentsForCommand('robo');
        $arguments->addOptionalArgument('--load-from=%s', $config['load_from']);
        $arguments->addOptionalArgument('%s', $config['task']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $process->getOutput());
        }

        return TaskResult::createPassed($this, $context);
    }
}
