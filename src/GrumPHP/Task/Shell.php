<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Shell task
 */
class Shell extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'shell';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'scripts' => [],
            'triggered_by' => array('php')
        ));

        $resolver->addAllowedTypes('scripts', array('array'));
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
            return;
        }

        $exceptions = array();
        foreach ($config['scripts'] as $script) {
            try {
                $this->runShell($script);
            } catch (RuntimeException $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        if (count($exceptions)) {
            throw new RuntimeException(implode(PHP_EOL, $exceptions));
        }
    }

    /**
     * @param $script
     */
    private function runShell($script)
    {
        $arguments = $this->processBuilder->createArgumentsForCommand('sh');
        $arguments->add($script);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getOutput());
        }
    }
}
