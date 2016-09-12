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
        ));

        $resolver->setAllowedTypes('jobs', array('int', 'null'));
        $resolver->setAllowedTypes('exclude', 'array');

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
        $fileNames = array_map(function (\SplFileInfo $file) {
            return $file->getPathname();
        }, iterator_to_array($context->getFiles()->extensions(array('php'))));

        $config = $this->getConfiguration();

        $args = $this->processBuilder->createArgumentsForCommand('parallel-lint');
        $args->add('--no-colors');
        if (!empty($config['jobs'])) {
            $args->add('-j');
            $args->add($config['jobs']);
        }
        $args->addArgumentArrayWithSeparatedValue('--exclude', $config['exclude']);
        $args->addArgumentArray('%s', $fileNames);

        $process = $this->processBuilder->buildProcess($args);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
