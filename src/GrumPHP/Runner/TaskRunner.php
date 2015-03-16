<?php

namespace GrumPHP\Runner;

use GrumPHP\Exception\FailureException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Finder\Finder;
use GrumPHP\Task\TaskInterface;

/**
 * Class TaskRunner
 *
 * @package GrumPHP\Runner
 */
class TaskRunner
{

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @var array
     */
    protected $tasks = array();

    /**
     * @param $finder
     */
    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

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
    public function run(array $files)
    {
        $failures = false;
        $messages = array();

        foreach ($this->getTasks() as $task) {
            try {
                $finder = $this->finder->create($files);
                $task->run($finder);
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
