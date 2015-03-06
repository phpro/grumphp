<?php

namespace GrumPHP;

use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\TaskInterface;

/**
 * Class TaskManager
 *
 * @package GrumPHP
 */
class TaskManager
{

    /**
     * @var GrumPHP
     */
    protected $config;

    /**
     * @var array
     */
    protected $tasks = array();

    /**
     * @param GrumPHP $config
     */
    public function __construct(GrumPHP $config)
    {
        $this->config = $config;
        $this->addTask($this->createTask('GrumPHP\Task\Phpcs'));
    }

    /**
     * @param       $class
     *
     * @return TaskInterface
     */
    public function createTask($class)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException('Invalid task class: ' . $class);
        }

        $rc = new \ReflectionClass($class);
        return $rc->newInstance($this->config);
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
     */
    public function run($files)
    {
        foreach ($this->getTasks() as $task) {
            if (!$task->isActive()) {
                continue;
            }
            $task->run($files);
        }
    }
}
