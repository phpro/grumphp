<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Phpspec task
 */
class Kahlan extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'kahlan';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config' => null,
            'src' => null,
            'spec' => null,
            'pattern' => null,
            'reporter' => null,
            'coverage' => null,
            'clover' => null,
            'istanbul' => null,
            'lcov' => null,
            'ff' => null,
            'no-colors' => null,
            'no-header' => null,
            'include' => null,
            'exclude' => null,
            'persistent' => null,
            'cc' => null,
            'autoclear' => null,
        ]);

        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('src', ['null', 'string']);
        $resolver->addAllowedTypes('spec', ['null', 'string']);
        $resolver->addAllowedTypes('pattern', ['null', 'string']);
        $resolver->addAllowedTypes('reporter', ['null', 'string']);
        $resolver->addAllowedTypes('coverage', ['null', 'string', 'int']);
        $resolver->addAllowedTypes('clover', ['null', 'string']);
        $resolver->addAllowedTypes('istanbul', ['null', 'string']);
        $resolver->addAllowedTypes('lcov', ['null', 'string']);
        $resolver->addAllowedTypes('ff', ['null', 'int']);
        $resolver->addAllowedTypes('ff', ['null', 'int']);
        $resolver->addAllowedTypes('no-colors', ['null', 'bool']);
        $resolver->addAllowedTypes('no-header', ['null', 'bool']);
        $resolver->addAllowedTypes('include', ['null', 'string', 'array']);
        $resolver->addAllowedTypes('exclude', ['null', 'string', 'array']);
        $resolver->addAllowedTypes('persistent', ['null', 'bool']);
        $resolver->addAllowedTypes('cc', ['null', 'bool']);
        $resolver->addAllowedTypes('autoclear', ['null', 'array']);

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
        $files = $context->getFiles()->name('*.php');
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('kahlan');
        $arguments->add('--no-interaction');

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
