<?php

namespace GrumPHP\Task;

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
        $resolver->setDefaults([
            'directory' => '.',
            'exclude' => ['vendor'],
            'names_exclude' => [],
            'fuzzy' => false,
            'min_lines' => 5,
            'min_tokens' => 70,
            'triggered_by' => ['php'],
        ]);

        $resolver->addAllowedTypes('directory', ['string']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('names_exclude', ['array']);
        $resolver->addAllowedTypes('fuzzy', ['bool']);
        $resolver->addAllowedTypes('min_lines', ['int']);
        $resolver->addAllowedTypes('min_tokens', ['int']);
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

        $arguments = $this->processBuilder->createArgumentsForCommand('phpcpd');
        $extensions = array_map(function ($extension) {
            return sprintf('*.%s', $extension);
        }, $config['triggered_by']);

        $arguments->addArgumentArray('--exclude=%s', $config['exclude']);
        $arguments->addArgumentArray('--names-exclude=%s', $config['names_exclude']);
        $arguments->addRequiredArgument('--min-lines=%u', $config['min_lines']);
        $arguments->addRequiredArgument('--min-tokens=%u', $config['min_tokens']);
        $arguments->addOptionalCommaSeparatedArgument('--names=%s', $extensions);
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
