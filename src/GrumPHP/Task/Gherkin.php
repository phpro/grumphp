<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Gherkin task
 */
class Gherkin extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'gherkin';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'directory' => 'features',
            'align' => null,
        ));

        $resolver->addAllowedTypes('directory', array('string'));
        $resolver->addAllowedTypes('align', array('null', 'string'));
        $resolver->addAllowedValues('align', array(null, 'left', 'right'));

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
        $files = $context->getFiles()->extensions(array('feature'));
        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('kawaii');
        $arguments->add('gherkin:check');
        $arguments->addOptionalArgument('--align=%s', $config['align']);
        $arguments->add($config['directory']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $process->getOutput());
        }

        return TaskResult::createPassed($this, $context);
    }
}
