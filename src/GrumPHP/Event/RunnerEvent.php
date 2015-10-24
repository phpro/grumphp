<?php

namespace GrumPHP\Event;

use GrumPHP\Collection\TasksCollection;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class RunnerEvent
 *
 * @package GrumPHP\Event
 */
class RunnerEvent extends Event
{
    /**
     * @var TasksCollection
     */
    private $tasks;

    /**
     * @param TasksCollection $tasks
     */
    public function __construct(TasksCollection $tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * @return TasksCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }
}
