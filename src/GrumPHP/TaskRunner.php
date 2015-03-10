<?php

namespace GrumPHP;

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
     */
    public function run($files)
    {
        foreach ($this->getTasks() as $task) {
            $task->run($files);
        }
    }
}
