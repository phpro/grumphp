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

    public function __construct(ContextInterface $taskContext, TestSuiteInterface $testSuite = null)
    {
        $this->taskContext = $taskContext;
        $this->testSuite = $testSuite;
    }

    public function getTaskContext(): ContextInterface
    {
        return $this->taskContext;
    }

    public function skipSuccessOutput(): bool
    {
        return $this->skipSuccessOutput;
    }

    public function setSkipSuccessOutput(bool $skipSuccessOutput): void
    {
        $this->skipSuccessOutput = $skipSuccessOutput;
    }

    public function hasTestSuite(): bool
    {
        return null !== $this->testSuite;
    }

    public function getTestSuite(): ?TestSuiteInterface
    {
        return $this->testSuite;
    }

    public function setTestSuite(?TestSuiteInterface $testSuite): void
    {
        $this->testSuite = $testSuite;
    }
}
