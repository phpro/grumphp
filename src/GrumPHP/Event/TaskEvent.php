<?php

namespace GrumPHP\Event;

use GrumPHP\Task\TaskInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class TaskEvent
 *
 * @package GrumPHP\Event
 */
class TaskEvent extends Event
{
    /**
     * @var TaskInterface
     */
    private $task;

    /**
     * @param TaskInterface $task
     */
    public function __construct(TaskInterface $task)
    {
        $this->task = $task;
    }

    /**
     * @return TaskInterface
     */
    public function getTask()
    {
        return $this->task;
    }
}
