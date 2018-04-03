<?php

declare(strict_types=1);

namespace GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Formatter\PhpCsFixerFormatter;
use GrumPHP\Process\AsyncProcessRunner;
use GrumPHP\Process\ProcessBuilder;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;

/**
 * Class PhpCsFixerRunner.
 */
abstract class AbstractPhpCsFixerTask implements TaskInterface
{
    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @var AsyncProcessRunner
     */
    protected $processRunner;

    /**
     * @var PhpCsFixerFormatter
     */
    protected $formatter;

    /**
     * PhpCsFixerRunner constructor.
     */
    public function __construct(
        GrumPHP $grumPHP,
        ProcessBuilder $processBuilder,
        AsyncProcessRunner $processRunner,
        PhpCsFixerFormatter $formatter
    ) {
        $this->processBuilder = $processBuilder;
        $this->processRunner = $processRunner;
        $this->formatter = $formatter;
        $this->grumPHP = $grumPHP;
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
    public function getConfiguration(): array
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    protected function runOnAllFiles(ContextInterface $context, ProcessArgumentsCollection $arguments): TaskResult
    {
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

    protected function runOnChangedFiles(
        ContextInterface $context,
        ProcessArgumentsCollection $arguments,
        FilesCollection $files
    ): TaskResult {
        $hasErrors = false;
        $messages = [];
        $suggestions = [];
        $processes = [];

        foreach ($files as $file) {
            $fileArguments = new ProcessArgumentsCollection($arguments->getValues());
            $fileArguments->add($file);
            $processes[] = $this->processBuilder->buildProcess($fileArguments);
        }

        $this->processRunner->run($processes);

        foreach ($processes as $process) {
            if (!$process->isSuccessful()) {
                $hasErrors = true;
                $messages[] = $this->formatter->format($process);
                $suggestions[] = $this->formatter->formatSuggestion($process);
            }
        }

        if ($hasErrors) {
            $errorMessage = $this->formatter->formatErrorMessage($messages, $suggestions);

            return TaskResult::createFailed($this, $context, $errorMessage);
        }

        return TaskResult::createPassed($this, $context);
    }
}
