<?php

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
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
    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'indent_size' => null,
            'indent_style' => null,
            'no_update_lock' => true,
            'verbose' => false,
        ]);

        $resolver->addAllowedTypes('indent_size', ['int', 'null']);
        $resolver->addAllowedTypes('indent_style', ['string', 'null']);
        $resolver->addAllowedValues('indent_style', ['tab', 'space', null]);
        $resolver->addAllowedTypes('no_update_lock', ['bool']);
        $resolver->addAllowedTypes('verbose', ['bool']);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'composer_normalize';
    }

    /**
     * {@inheritdoc}
     */
    public function run(ContextInterface $context): TaskResultInterface
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

        if ($config['indent_size'] !== null && $config['indent_style'] !== null) {
            $arguments->addOptionalArgument('--indent-style=%s', $config['indent_style']);
            $arguments->addOptionalArgument('--indent-size=%s', $config['indent_size']);
        }

        $arguments->addOptionalArgument('--no-update-lock', !$config['no_update_lock']);
        $arguments->addOptionalArgument('-q', !$config['verbose']);

        $arguments->add('normalize');

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            $messages = [$this->formatter->format($process)];

            return TaskResult::createFailed($this, $context, $messages);
        }

        return TaskResult::createPassed($this, $context);
    }
}
