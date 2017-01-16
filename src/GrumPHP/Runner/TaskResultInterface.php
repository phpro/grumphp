<?php
namespace GrumPHP\Runner;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

interface TaskResultInterface
{
    /**
     * @return TaskInterface
     */
    public function getTask();

    /**
     * @return int
     */
    public function getResultCode();

    /**
     * @return bool
     */
    public function isPassed();

    /**
     * @return bool
     */
    public function isBlocking();

    /**
     * @return null|string
     */
    public function getMessage();

    /**
     * @return ContextInterface
     */
    public function getContext();
}
