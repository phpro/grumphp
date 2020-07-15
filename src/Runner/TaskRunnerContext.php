<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\TestSuite\TestSuiteInterface;

/**
 * @psalm-readonly
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
    private $taskNames;

    /**
     * @var TasksCollection
     */
    private $tasks;

    /**
     * @param string[] $taskNames
     */
    public function __construct(
        ContextInterface $taskContext,
        TestSuiteInterface $testSuite = null,
        array $taskNames = []
    ) {
        $this->taskContext = $taskContext;
        $this->testSuite = $testSuite;
        $this->taskNames = $taskNames;
        $this->tasks = new TasksCollection();
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
    public function getTaskNames(): array
    {
        return $this->taskNames;
    }

    public function hasTaskNames(): bool
    {
        return !empty($this->taskNames);
    }

    public function getTasks(): TasksCollection
    {
        return $this->tasks;
    }

    public function withTasks(TasksCollection $tasks): self
    {
        $new = clone $this;
        $new->tasks = $tasks;

        return $new;
    }
}
