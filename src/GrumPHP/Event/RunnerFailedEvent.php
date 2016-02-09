<?php

namespace GrumPHP\Event;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Task\Context\ContextInterface;

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
     * @param TasksCollection  $tasks
     * @param ContextInterface $context
     * @param array            $messages
     */
    public function __construct(TasksCollection $tasks, ContextInterface $context, array $messages)
    {
        parent::__construct($tasks, $context);
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
