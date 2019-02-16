<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ComposerNormalize.
 */
class ComposerNormalize extends AbstractExternalTask
{
    /**
     * {@inheritdoc}
     */
    public function canRunInContext(ContextInterface $context)
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableOptions()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'indent_size' => 4,
            'indent_style' => 'space',
            'update_lock' => true,
            'verbose' => false,
        ]);

        $resolver->addAllowedValues('indent_style', ['tab', 'space']);
        $resolver->addAllowedTypes('indent_size', ['int']);
        $resolver->addAllowedTypes('update_lock', ['bool']);
        $resolver->addAllowedTypes('verbose', ['bool']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'composer_normalize';
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context)
    {
        $config = $this->getConfiguration();
        $files = $context->getFiles()
            ->path(pathinfo('composer.json', PATHINFO_DIRNAME))
            ->name(pathinfo('composer.json', PATHINFO_BASENAME));

        if (0 === count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('composer');

        $arguments->add('--dry-run');
        $arguments->addOptionalArgument('--indent-style=%s', $config['indent_style']);
        $arguments->addOptionalArgument('--indent-size=%s', $config['indent_size']);

        if ($config['update_lock'] === false) {
            $arguments->add('--no-update-lock');
        }

        $arguments->add('normalize');

        if ($config['verbose'] !== true) {
            $arguments->add('-q');
        }

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            $messages = [$this->formatter->format($process)];
            $suggestions = [$this->formatter->formatSuggestion($process)];
            $errorMessage = $this->formatter->formatErrorMessage($messages, $suggestions);

            return TaskResult::createFailed($this, $context, $errorMessage);
        }

        return TaskResult::createPassed($this, $context);
    }
}
