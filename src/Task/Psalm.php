<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Psalm extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'config' => null,
            'ignore_patterns' => [],
            'no_cache' => false,
            'report' => null,
            'output_format' => null,
            'threads' => null,
            'triggered_by' => ['php'],
            'show_info' => false,
        ]);

        $resolver->addAllowedTypes('config', ['null', 'string']);
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->addAllowedTypes('no_cache', ['bool']);
        $resolver->addAllowedTypes('report', ['null', 'string']);
        $resolver->addAllowedTypes('output_format', ['null', 'string']);
        $resolver->addAllowedTypes('threads', ['null', 'int']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('show_info', ['bool']);

        $resolver->setAllowedValues(
            'output_format',
            [null, 'compact', 'console', 'emacs', 'json', 'pylint', 'xml', 'checkstyle', 'junit', 'sonarqube']
        );

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return ($context instanceof GitPreCommitContext || $context instanceof RunContext);
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context
            ->getFiles()
            ->notPaths($config['ignore_patterns'])
            ->extensions($config['triggered_by']);

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('psalm');
        $arguments->addOptionalArgument('--output-format=%s', $config['output_format']);
        $arguments->addOptionalArgument('--config=%s', $config['config']);
        $arguments->addOptionalArgument('--report=%s', $config['report']);
        $arguments->addOptionalArgument('--no-cache', $config['no_cache']);
        $arguments->addOptionalArgument('--threads=%d', $config['threads']);
        $arguments->addOptionalBooleanArgument('--show-info=%s', $config['show_info'], 'true', 'false');

        if ($context instanceof GitPreCommitContext) {
            $arguments->addFiles($files);
        }

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
