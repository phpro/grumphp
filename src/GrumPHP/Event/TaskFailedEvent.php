<?php

namespace GrumPHP\Event;

use Exception;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

class TaskFailedEvent extends TaskEvent
{
    /**
     * @var Exception
     */
    private $exception;

    /**
     * @param TaskInterface    $task
     * @param ContextInterface $context
     * @param Exception        $exception
     */
    public function __construct(TaskInterface $task, ContextInterface $context, Exception $exception)
    {
        parent::__construct($task, $context);

        $this->exception = $exception;
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}
