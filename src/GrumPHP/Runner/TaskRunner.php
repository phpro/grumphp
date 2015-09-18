<?php

namespace GrumPHP\Runner;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Exception\FailureException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;

/**
 * Class TaskRunner
 *
 * @package GrumPHP\Runner
 */
class TaskRunner
{
    /**
     * @var TasksCollection|TaskInterface[]
     */
    protected $tasks;

    /**
     * @constructor
     */
    public function __construct()
    {
        $this->tasks = new TasksCollection();
    }

    /**
     * @param TaskInterface $task
     */
    public function addTask(TaskInterface $task)
    {
        if ($this->tasks->contains($task)) {
            return;
        }

        $this->tasks->add($task);
    }

    /**
     * @return TasksCollection|TaskInterface[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param ContextInterface $context
     *
     * @throws FailureException if any of the tasks fail
     */
    public function run(ContextInterface $context)
    {
        $failures = false;
        $messages = array();

        $tasks = $this->tasks->filterByContext($context);
        foreach ($tasks as $task) {
            try {
                $task->run($context);
            } catch (RuntimeException $e) {
                $failures = true;
                $messages[] = $e->getMessage();
            }
        }

        if ($failures) {
            throw new FailureException(implode(PHP_EOL, $messages));
        }
    }
}
