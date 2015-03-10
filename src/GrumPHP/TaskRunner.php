<?php

namespace GrumPHP;

use GrumPHP\Exception\FailureException;
use GrumPHP\Task\TaskInterface;

/**
 * Class TaskRunner
 *
 * @package GrumPHP
 */
class TaskRunner
{

    /**
     * @var array
     */
    protected $tasks = array();

    /**
     * @param TaskInterface $task
     *
     * @return $this
     */
    public function addTask(TaskInterface $task)
    {
        if (in_array($task, $this->tasks)) {
            return $this;
        }

        $this->tasks[] = $task;

        return $this;
    }

    /**
     * @return TaskInterface[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param array $files
     *
     * @throws FailureException if any of the tasks fail
     */
    public function run($files)
    {
        $failures = false;
        $messages = array();

        foreach ($this->getTasks() as $task) {
            try {
                $task->run($files);
            } catch (\RuntimeException $e) {
                $failures = true;
                $messages[] = $e->getMessage();
            }
        }

        if ($failures) {
            throw new FailureException(implode("\n", $messages));
        }
    }
}
