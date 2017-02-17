<?php

namespace GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class TaskResult implements TaskResultInterface
{
    const SKIPPED = -100;
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
    private function __construct($resultCode, TaskInterface $task, ContextInterface $context, $message = null)
    {
        $this->resultCode = $resultCode;
        $this->task = $task;
        $this->context = $context;
        $this->message = $message;
    }

    /**
     * @param TaskInterface    $task
     * @param ContextInterface $context
     *
     * @return TaskResult
     */
    public static function createSkipped(TaskInterface $task, ContextInterface $context)
    {
        return new self(self::SKIPPED, $task, $context);
    }

    /**
     * @param TaskInterface    $task
     * @param ContextInterface $context
     *
     * @return TaskResult
     */
    public static function createPassed(TaskInterface $task, ContextInterface $context)
    {
        return new self(self::PASSED, $task, $context, null);
    }

    /**
     * @param TaskInterface    $task
     * @param ContextInterface $context
     * @param string           $message
     *
     * @return TaskResult
     */
    public static function createFailed(TaskInterface $task, ContextInterface $context, $message)
    {
        return new self(self::FAILED, $task, $context, $message);
    }

    /**
     * @param TaskInterface    $task
     * @param ContextInterface $context
     * @param string           $message
     *
     * @return TaskResult
     */
    public static function createNonBlockingFailed(TaskInterface $task, ContextInterface $context, $message)
    {
        return new self(self::NONBLOCKING_FAILED, $task, $context, $message);
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

    public function hasFailed()
    {
        return self::FAILED === $this->getResultCode() || self::NONBLOCKING_FAILED === $this->getResultCode();
    }

    /**
     * @return bool
     */
    public function isBlocking()
    {
        return $this->getResultCode() >= self::FAILED;
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
