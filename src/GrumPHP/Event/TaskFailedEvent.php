<?php

namespace GrumPHP\Event;

use Exception;
use GrumPHP\Task\TaskInterface;

/**
 * Class TaskFailedEvent
 *
 * @package GrumPHP\Event
 */
class TaskFailedEvent extends TaskEvent
{
    /**
     * @var Exception
     */
    private $exception;

    /**
     * @param TaskInterface $task
     * @param Exception     $exception
     */
    public function __construct(TaskInterface $task, Exception $exception)
    {
        parent::__construct($task);

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
