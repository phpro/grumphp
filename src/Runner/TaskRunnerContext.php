<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\TestSuite\TestSuiteInterface;

/**
 * @psalm-immutable
 */
class TaskRunnerContext
{
    /**
     * @var ContextInterface
     */
    private $taskContext;

    /**
     * @var bool
     */
    private $skipSuccessOutput = false;

    /**
     * @var null|TestSuiteInterface
     */
    private $testSuite;

    /**
     * @var string[]
     */
    private $tasks;

    /**
     * @psalm-param string[] $tasks
     */
    public function __construct(
        ContextInterface $taskContext,
        TestSuiteInterface $testSuite = null,
        array $tasks = []
    ) {
        $this->taskContext = $taskContext;
        $this->testSuite = $testSuite;
        $this->tasks = $tasks;
    }

    public function getTaskContext(): ContextInterface
    {
        return $this->taskContext;
    }

    public function skipSuccessOutput(): bool
    {
        return $this->skipSuccessOutput;
    }

    public function withSkippedSuccessOutput(bool $skipSuccessOutput): self
    {
        $new = clone $this;
        $new->skipSuccessOutput = $skipSuccessOutput;

        return $new;
    }

    public function hasTestSuite(): bool
    {
        return null !== $this->testSuite;
    }

    public function getTestSuite(): ?TestSuiteInterface
    {
        return $this->testSuite;
    }

    /**
     * @return string[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function hasTasks(): bool
    {
        return !empty($this->tasks);
    }
}
