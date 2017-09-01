<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PhpMnd task
 */
class PhpMnd extends AbstractExternalTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'phpmnd';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'directory' => '.',
            'exclude' => [],
            'exclude_name' => [],
            'exclude_path' => [],
            'extensions' => [],
            'hint' => false,
            'ignore_numbers' => [],
            'ignore_strings' => [],
            'strings' => false,
            'triggered_by' => ['php']
        ]);

        $resolver->addAllowedTypes('directory', ['string']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('exclude_name', ['array']);
        $resolver->addAllowedTypes('exclude_path', ['array']);
        $resolver->addAllowedTypes('extensions', ['array']);
        $resolver->addAllowedTypes('hint', ['bool']);
        $resolver->addAllowedTypes('ignore_numbers', ['array']);
        $resolver->addAllowedTypes('ignore_strings', ['array']);
        $resolver->addAllowedTypes('strings', ['bool']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

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

        $config = $this->getConfiguration();

        $arguments = $this->processBuilder->createArgumentsForCommand('phpmnd');
        $arguments->addArgumentArray('--exclude=%s', $config['exclude']);
        $arguments->addArgumentArray('--exclude-file=%s', $config['exclude_name']);
        $arguments->addArgumentArray('--exclude-path=%s', $config['exclude_path']);
        $arguments->addOptionalCommaSeparatedArgument('--extensions=%s', $config['extensions']);
        $arguments->addOptionalArgument('--hint', $config['hint']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore-numbers=%s', $config['ignore_numbers']);
        $arguments->addOptionalCommaSeparatedArgument('--ignore-strings=%s', $config['ignore_strings']);
        $arguments->addOptionalArgument('--strings', $config['strings']);
        $arguments->addOptionalCommaSeparatedArgument('--suffixes=%s', $config['triggered_by']);
        $arguments->add('--non-zero-exit-on-violation');
        $arguments->add($config['directory']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
