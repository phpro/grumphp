<?php

namespace GrumPHP\Task\Git;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Git Conflict Task
 *
 * @package GrumPHP\Task\Git
 */
class Conflict extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'git_conflict';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        return new OptionsResolver();
    }

    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return ($context instanceof GitPreCommitContext);
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $files = $context->getFiles();

        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('git');
        $arguments->add('diff');
        $arguments->add('--name-only');
        $arguments->add('--diff-filter=U');
        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if ($process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, sprintf(
                'You have merge conflicts in the following files, please resolve them before you can continue%s%s',
                PHP_EOL,
                $this->formatter->format($process)
            ));
        }

        return TaskResult::createPassed($this, $context);
    }
}
