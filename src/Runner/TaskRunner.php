<?php

declare(strict_types=1);

namespace GrumPHP\Runner;

use GrumPHP\Collection\TaskResultCollection;
use GrumPHP\Collection\TasksCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Event\Dispatcher\EventDispatcherInterface;
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
     */
    public function __construct(GrumPHP $grumPHP, EventDispatcherInterface $eventDispatcher)
    {
        $this->tasks = new TasksCollection();
        $this->eventDispatcher = $eventDispatcher;
        $this->grumPHP = $grumPHP;
    }

    public function addTask(TaskInterface $task): void
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

    public function run(TaskRunnerContext $runnerContext): TaskResultCollection
    {
        $context = $runnerContext->getTaskContext();
        $tasks = $this->tasks
            ->filterByContext($runnerContext->getTaskContext())
            ->filterByTestSuite($runnerContext->getTestSuite())
            ->filterByTaskNames($runnerContext->getTasks())
            ->sortByPriority($this->grumPHP);
        $taskResults = new TaskResultCollection();

        $this->eventDispatcher->dispatch(new RunnerEvent($tasks, $context, $taskResults), RunnerEvents::RUNNER_RUN);
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
                new RunnerFailedEvent($tasks, $context, $taskResults),
                RunnerEvents::RUNNER_FAILED
            );

            return $taskResults;
        }

        $this->eventDispatcher->dispatch(
            new RunnerEvent($tasks, $context, $taskResults),
            RunnerEvents::RUNNER_COMPLETE
        );

        return $taskResults;
    }

    /**
     * @throws RuntimeException
     */
    private function runTask(TaskInterface $task, ContextInterface $context): TaskResultInterface
    {
        try {
            $this->eventDispatcher->dispatch(new TaskEvent($task, $context), TaskEvents::TASK_RUN);
            $result = $task->run($context);
        } catch (PlatformException $e) {
            $this->eventDispatcher->dispatch(new TaskEvent($task, $context), TaskEvents::TASK_SKIPPED);

            return TaskResult::createSkipped($task, $context);
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

        if ($result->hasFailed()) {
            $e = new RuntimeException($result->getMessage());
            $this->eventDispatcher->dispatch(new TaskFailedEvent($task, $context, $e), TaskEvents::TASK_FAILED);

            return $result;
        }

        $this->eventDispatcher->dispatch(new TaskEvent($task, $context), TaskEvents::TASK_COMPLETE);

        return $result;
    }
}
