<?php

namespace GrumPHP\Event;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\EventDispatcher\Event;

class TaskEvent extends Event
{
    /**
     * @var TaskInterface
     */
    private $task;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @param TaskInterface    $task
     * @param ContextInterface $context
     */
    public function __construct(TaskInterface $task, ContextInterface $context)
    {
        $this->task = $task;
        $this->context = $context;
    }

    /**
     * @return TaskInterface
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
