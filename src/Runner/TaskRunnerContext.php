<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\TestSuite\TestSuiteInterface;

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

    public function setSkipSuccessOutput(bool $skipSuccessOutput)
    {
        $this->skipSuccessOutput = $skipSuccessOutput;
    }

    public function hasTestSuite(): bool
    {
        return null !== $this->testSuite;
    }

    /**
     * @return null|TestSuiteInterface
     */
    public function getTestSuite()
    {
        return $this->testSuite;
    }

    /**
     * @param null|TestSuiteInterface $testSuite
     */
    public function setTestSuite(TestSuiteInterface $testSuite)
    {
        $this->testSuite = $testSuite;
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
