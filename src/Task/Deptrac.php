<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Deptrac task
 */
class Deptrac extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'deptrac';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();

        return $resolver;
    }

    /**
     * This methods specifies if a task can run in a specific context.
     *
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResultInterface
     */
    public function run(ContextInterface $context)
    {
//        $config = $this->getConfiguration();

        $files = $context->getFiles()->name('*.php');
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('deptrac');

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
