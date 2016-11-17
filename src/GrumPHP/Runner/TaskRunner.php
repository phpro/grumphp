<?php

namespace GrumPHP\Runner;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\RunnerEvent;
use GrumPHP\Event\RunnerEvents;
use GrumPHP\Event\RunnerFailedEvent;
use GrumPHP\Event\TaskEvent;
use GrumPHP\Event\TaskEvents;
use GrumPHP\Event\TaskFailedEvent;
use GrumPHP\Exception\PlatformException;
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
     * @return TaskResultCollection
     */
    public function run(ContextInterface $context)
    {
        $tasks = $this->tasks->filterByContext($context)->sortByPriority($this->grumPHP);
        $taskResults = new TaskResultCollection();

        $this->eventDispatcher->dispatch(RunnerEvents::RUNNER_RUN, new RunnerEvent($tasks, $context, $taskResults));
        foreach ($tasks as $task) {
            try {
                $taskResult = $this->runTask($task, $context);
            } catch (RuntimeException $e) {
                $taskResult = TaskResult::createFailed($task, $context, $e->getMessage());
            }

            $taskResults->add($taskResult);
            if (!$taskResult->isPassed() && $taskResult->isBlocking() && $this->grumPHP->stopOnFailure()) {
                break;
            }
        }

        if ($taskResults->isFailed()) {
            $this->eventDispatcher->dispatch(
                RunnerEvents::RUNNER_FAILED,
                new RunnerFailedEvent($tasks, $context, $taskResults)
            );

            return $taskResults;
        }

        $this->eventDispatcher->dispatch(
            RunnerEvents::RUNNER_COMPLETE,
            new RunnerEvent($tasks, $context, $taskResults)
        );

        return $taskResults;
    }

    /**
     * @param TaskInterface    $task
     * @param ContextInterface $context
     *
     * @return TaskResultInterface
     * @throws RuntimeException
     */
    private function runTask(TaskInterface $task, ContextInterface $context)
    {
        try {
            $this->eventDispatcher->dispatch(TaskEvents::TASK_RUN, new TaskEvent($task, $context));
            $result = $task->run($context);
        } catch (PlatformException $e) {
            $result = TaskResult::createNonBlockingFailed($task, $context, $e->getMessage());
        } catch (RuntimeException $e) {
            $result = TaskResult::createFailed($task, $context, $e->getMessage());
        }
        
        if (!$result instanceof TaskResultInterface) {
            throw RuntimeException::invalidTaskReturnType($task);
        }

        if (!$result->isPassed() && !$this->grumPHP->isBlockingTask($task->getName())) {
            $result = TaskResult::createNonBlockingFailed(
                $result->getTask(),
                $result->getContext(),
                $result->getMessage()
            );
        }
        
        if (!$result->isPassed()) {
            $e = new RuntimeException($result->getMessage());
            $this->eventDispatcher->dispatch(TaskEvents::TASK_FAILED, new TaskFailedEvent($task, $context, $e));

            return $result;
        }

        $this->eventDispatcher->dispatch(TaskEvents::TASK_COMPLETE, new TaskEvent($task, $context));

        return $result;
    }
}
