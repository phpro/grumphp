<?php

namespace GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
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
        $resolver->setDefaults([
            'scripts' => [],
            'triggered_by' => ['php']
        ]);

        $resolver->addAllowedTypes('scripts', ['array']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->setNormalizer('scripts', function ($resolver, $scripts) {
            return array_map(function ($script) {
                return is_string($script) ? (array) $script : $script;
            }, $scripts);
        });

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

        $exceptions = [];
        foreach ($config['scripts'] as $script) {
            try {
                $this->runShell($script);
            } catch (RuntimeException $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        if (count($exceptions)) {
            return TaskResult::createFailed($this, $context, implode(PHP_EOL, $exceptions));
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param array $scriptArguments
     */
    private function runShell(array $scriptArguments)
    {
        $arguments = $this->processBuilder->createArgumentsForCommand('sh');
        $arguments->addArgumentArray('%s', $scriptArguments);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($this->formatter->format($process));
        }
    }
}
