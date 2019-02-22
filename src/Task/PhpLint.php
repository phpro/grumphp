<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\InputStream;

class PhpLint extends AbstractExternalTask
{
    public function getName(): string
    {
        return 'phplint';
    }

    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'jobs' => null,
            'exclude' => [],
            'ignore_patterns' => [],
            'triggered_by' => ['php', 'phtml', 'php3', 'php4', 'php5'],
        ]);

        $resolver->setAllowedTypes('jobs', ['int', 'null']);
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
        $config = $this->getConfiguration();

        $files = $context
            ->getFiles()
            ->notPaths($config['ignore_patterns'])
            ->extensions($config['triggered_by']);

        $arguments = $this->processBuilder->createArgumentsForCommand('parallel-lint');
        $arguments->add('--no-colors');
        $arguments->addOptionalArgumentWithSeparatedValue('-j', $config['jobs']);
        $arguments->addArgumentArrayWithSeparatedValue('--exclude', $config['exclude']);
        $arguments->add('--stdin');

        $inputStream = new InputStream();
        $process = $this->processBuilder->buildProcess($arguments);
        $process->setInput($inputStream);
        $process->start();
        $inputStream->write(\implode($files->toArray(), PHP_EOL));
        $inputStream->close();
        $process->wait();

        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }
}
