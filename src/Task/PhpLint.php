<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Process\InputWritingProcessRunner;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;

class PhpLint extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'jobs' => null,
            'short_open_tag' => false,
            'exclude' => [],
            'ignore_patterns' => [],
            'triggered_by' => ['php', 'phtml', 'php3', 'php4', 'php5'],
        ]);

        $resolver->setAllowedTypes('jobs', ['int', 'null']);
        $resolver->setAllowedTypes('short_open_tag', 'bool');
        $resolver->setAllowedTypes('exclude', 'array');
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->setAllowedTypes('triggered_by', 'array');

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof RunContext || $context instanceof GitPreCommitContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfig()->getOptions();

        $files = $context
            ->getFiles()
            ->notPaths($config['ignore_patterns'])
            ->extensions($config['triggered_by']);

        if ($files->isEmpty()) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('parallel-lint');
        $arguments->add('--no-colors');
        $arguments->addOptionalArgumentWithSeparatedValue('-j', $config['jobs']);
        $arguments->addOptionalArgument('--short', $config['short_open_tag']);
        $arguments->addArgumentArrayWithSeparatedValue('--exclude', $config['exclude']);
        $arguments->add('--stdin');

        $process = InputWritingProcessRunner::run(
            function () use ($arguments): Process {
                return $this->processBuilder->buildProcess($arguments);
            },
            static function () use ($files) {
                yield $files->toFileList();
            }
        );

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
