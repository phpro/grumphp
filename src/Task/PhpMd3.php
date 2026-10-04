<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
class PhpMd3 extends AbstractExternalTask
{
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'whitelist_patterns' => [],
            'exclude' => [],
            'report_format' => 'text',
            'ruleset' => ['phpmd.xml.dist'],
            'triggered_by' => ['php'],
        ]);

        $resolver->addAllowedTypes('whitelist_patterns', ['array']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('report_format', ['string']);
        $resolver->addAllowedValues('report_format', ['text', 'ansi']);
        $resolver->addAllowedTypes('ruleset', ['array']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

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
    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $whitelistPatterns = $config['whitelist_patterns'];
        $extensions = $config['triggered_by'];

        $files = $context->getFiles();
        if (\count($whitelistPatterns)) {
            $files = $files->paths($whitelistPatterns);
        }
        $files = $files->extensions($extensions);

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('phpmd');
        $arguments->add('analyze');
        $arguments->addOptionalArgument('--format=%s', $config['report_format']);

        foreach ($config['ruleset'] as $ruleset) {
            $arguments->addOptionalArgument('--ruleset=%s', $ruleset);
        }

        foreach ($config['exclude'] as $exclude) {
            $arguments->addOptionalArgument('--exclude=%s', $exclude);
        }

        foreach ($extensions as $extension) {
            $arguments->addOptionalArgument('--suffixes=%s', $extension);
        }

        $arguments->addFiles($files);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
