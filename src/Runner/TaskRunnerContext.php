<?php

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
     * @var string[]
     */
    private $tasks = [];

    /**
     * @var null|TestSuiteInterface
     */
    private $testSuite = null;

    /**
     * @var ParallelOptions $parallelOptions
     */
    private $parallelOptions = null;

    /**
     * TaskRunnerContext constructor.
     *
     * @param ContextInterface $taskContext
     * @param string[] $tasks
     * @param TestSuiteInterface $testSuite
     * @param ParallelOptions|null $parallelOptions
     */
    public function __construct(
        ContextInterface $taskContext,
        array $tasks,
        TestSuiteInterface $testSuite = null,
        ParallelOptions $parallelOptions = null
    ) {
        $this->taskContext     = $taskContext;
        $this->tasks           = $tasks;
        $this->testSuite       = $testSuite;
        $this->parallelOptions = $parallelOptions;
    }

    /**
     * @return ContextInterface
     */
    public function getTaskContext()
    {
        return $this->taskContext;
    }

    /**
     * @return bool
     */
    public function skipSuccessOutput()
    {
        return $this->skipSuccessOutput;
    }

    /**
     * @param bool $skipSuccessOutput
     */
    public function setSkipSuccessOutput($skipSuccessOutput)
    {
        $this->skipSuccessOutput = (bool) $skipSuccessOutput;
    }

    /**
     * @return bool
     */
    public function hasTestSuite()
    {
        return $this->testSuite !== null;
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

    /**
     * @return ParallelOptions|null
     */
    public function getParallelOptions()
    {
        return $this->parallelOptions;
    }

    public function runInParallel():bool
    {
        return $this->getParallelOptions() !== null;
    }
}
