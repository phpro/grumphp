<?php

namespace GrumPHP\Runner;

use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\RunnerFailedEvent;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Event\TaskFailedEvent;
use GrumPHP\Exception\FailureException;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    private $tasks;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var GrumPHP
     */
    private $grumPHP;

    /**
     * @constructor
     *
     * @param GrumPHP                  $grumPHP
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(GrumPHP $grumPHP, EventDispatcherInterface $eventDispatcher)
    {
        $this->tasks = new TasksCollection();
        $this->eventDispatcher = $eventDispatcher;
        $this->grumPHP = $grumPHP;
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
        $tasks = $this->tasks->filterByContext($context)->sortByPriority($this->grumPHP);

        $this->eventDispatcher->dispatch(RunnerEvents::RUNNER_RUN, new RunnerEvent($tasks));
        foreach ($tasks as $task) {
            try {
                $this->eventDispatcher->dispatch(TaskEvents::TASK_RUN, new TaskEvent($task));
                $task->run($context);
                $this->eventDispatcher->dispatch(TaskEvents::TASK_COMPLETE, new TaskEvent($task));
            } catch (RuntimeException $e) {
                $this->eventDispatcher->dispatch(TaskEvents::TASK_FAILED, new TaskFailedEvent($task, $e));
                $messages[] = $e->getMessage();
                $failures = true;

                if ($this->grumPHP->stopOnFailure()) {
                    break;
                }
            }
        }

        if ($failures) {
            $this->eventDispatcher->dispatch(RunnerEvents::RUNNER_FAILED, new RunnerFailedEvent($tasks, $messages));
            throw new FailureException(implode(PHP_EOL, $messages));
        }

        $this->eventDispatcher->dispatch(RunnerEvents::RUNNER_COMPLETE, new RunnerEvent($tasks));
    }
}
