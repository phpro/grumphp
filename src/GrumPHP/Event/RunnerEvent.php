<?php

namespace GrumPHP\Event;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Task\Context\ContextInterface;
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
     * @var ContextInterface
     */
    private $context;

    /**
     * @param TasksCollection  $tasks
     * @param ContextInterface $context
     */
    public function __construct(TasksCollection $tasks, ContextInterface $context)
    {
        $this->tasks = $tasks;
        $this->context = $context;
    }

    /**
     * @return TasksCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }
}
