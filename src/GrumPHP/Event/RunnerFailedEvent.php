<?php

namespace GrumPHP\Event;

use GrumPHP\Collection\TasksCollection;

/**
 * Class RunnerFailedEvent
 *
 * @package GrumPHP\Event
 */
class RunnerFailedEvent extends RunnerEvent
{
    /**
     * @var array
     */
    private $messages;

    /**
     * @param TasksCollection $tasks
     * @param array           $messages
     */
    public function __construct(TasksCollection $tasks, array $messages)
    {
        parent::__construct($tasks);
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
