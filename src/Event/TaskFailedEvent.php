<?php

declare(strict_types=1);

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

    public function __construct(TaskInterface $task, ContextInterface $context, Exception $exception)
    {
        parent::__construct($task, $context);

        $this->exception = $exception;
    }

    public function getException(): Exception
    {
        return $this->exception;
    }
}
