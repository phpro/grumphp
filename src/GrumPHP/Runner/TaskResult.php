<?php

namespace GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class TaskResult
{
    const PASSED = 0;
    const NONBLOCKING_FAILED = 90;
    const FAILED = 99;

    /**
     * @var integer
     */
    private $resultCode;
    /**
     * @var TaskInterface
     */
    private $task;
    /**
     * @var ContextInterface
     */
    private $context;
    /**
     * @var string|null
     */
    private $message;

    /**
     * Initializes test result.
     *
     * @param integer $resultCode
     * @param TaskInterface $task
     * @param ContextInterface $context
     * @param string|null $message
     */
    public function __construct($resultCode, TaskInterface $task, ContextInterface $context, $message = null)
    {
        $this->resultCode = $resultCode;
        $this->task = $task;
        $this->context = $context;
        $this->message = $message;
    }

    /**
     * @return TaskInterface
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return int
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * @return bool
     */
    public function isPassed()
    {
        return self::PASSED === $this->getResultCode();
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
