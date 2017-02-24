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
     * @var null|TestSuiteInterface
     */
    private $testSuite = null;

    /**
     * TaskRunnerContext constructor.
     *
     * @param ContextInterface   $taskContext
     * @param TestSuiteInterface $testSuite
     */
    public function __construct(ContextInterface $taskContext, TestSuiteInterface $testSuite = null)
    {
        $this->taskContext = $taskContext;
        $this->testSuite = $testSuite;
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
        $this->skipSuccessOutput = (bool)$skipSuccessOutput;
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
}
