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

    public function __construct(ContextInterface $taskContext, TestSuiteInterface $testSuite = null, array $tasks = null)
    {
        $this->taskContext = $taskContext;
        $this->testSuite = $testSuite;
        if($tasks === null){
            $tasks = [];
        }
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
     * @return TestSuiteInterface|null
     */
    public function getTestSuite()
    {
        return $this->testSuite;
    }

    /**
     * @param TestSuiteInterface|null $testSuite
     */
    public function setTestSuite(TestSuiteInterface $testSuite)
    {
        $this->testSuite = $testSuite;
    }

    /**
     * @return string[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @return bool
     */
    public function hasTasks()
    {
        return !empty($this->tasks);
    }
}
