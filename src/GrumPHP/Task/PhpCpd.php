<?php

namespace GrumPHP\Task;

use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PhpCpd task
 */
class PhpCpd extends AbstractExternalTask
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'phpcpd';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'directory' => '.',
            'exclude' => array('vendor'),
            'fuzzy' => false,
            'min_lines' => 5,
            'min_tokens' => 70,
            'triggered_by' => array('php'),
        ));

        $resolver->addAllowedTypes('directory', array('string'));
        $resolver->addAllowedTypes('exclude', array('array'));
        $resolver->addAllowedTypes('fuzzy', array('bool'));
        $resolver->addAllowedTypes('min_lines', array('int'));
        $resolver->addAllowedTypes('min_tokens', array('int'));
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

        $arguments = $this->processBuilder->createArgumentsForCommand('phpcpd');

        $arguments->addArgumentArray('--exclude=%s', $config['exclude']);
        $arguments->addRequiredArgument('--min-lines=%u', $config['min_lines']);
        $arguments->addRequiredArgument('--min-tokens=%u', $config['min_tokens']);
        $arguments->addOptionalArgument('--fuzzy', $config['fuzzy']);
        $arguments->add($config['directory']);

        $process = $this->processBuilder->buildProcess($arguments);

        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
