<?php

namespace GrumPHP\Runner;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Exception\FailureException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\TaskInterface;

/**
 * Class TaskRunner
 *
 * @package GrumPHP\Runner
 */
class TaskRunner
{

    /**
     * @var TaskInterface[]
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
     * @param FilesCollection $files
     *
     * @throws FailureException if any of the tasks fail
     */
    public function run(FilesCollection $files)
    {
        $failures = false;
        $messages = array();

        foreach ($this->getTasks() as $task) {
            if ($task->isEnabled()) {
                try {
                    $task->run($files);
                } catch (RuntimeException $e) {
                    $failures = true;
                    $messages[] = $e->getMessage();
                }
            }
        }

        if ($failures) {
            throw new FailureException(implode(PHP_EOL, $messages));
        }
    }
}
