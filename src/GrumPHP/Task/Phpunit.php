<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Phpunit task
 */
class Phpunit extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phpunit';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'config_file' => null,
        ));

        $resolver->addAllowedTypes('config_file', array('null', 'string'));

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
            return;
        }

        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('phpunit');
        $arguments->addOptionalArgument('--configuration=%s', $config['config_file']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
