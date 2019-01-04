<?php

declare(strict_types=1);

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
use GrumPHP\Task\ParallelTaskInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;

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

    public function __construct(GrumPHP $grumPHP, EventDispatcherInterface $eventDispatcher)
    {
        $this->tasks           = new TasksCollection();
        $this->eventDispatcher = $eventDispatcher;
        $this->grumPHP         = $grumPHP;
    }

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

    public function run(TaskRunnerContext $runnerContext): TaskResultCollection
    {
        $context = $runnerContext->getTaskContext();

        $tasks = $this->tasks
            ->filterByContext($runnerContext->getTaskContext())
            ->filterByTestSuite($runnerContext->getTestSuite())
            ->filterByTaskNames($runnerContext->getTasks())
            ->sortByPriority($this->grumPHP)
        ;

        if ($runnerContext->runInParallel()) {
            return $this->runTasksParallely($tasks, $context, $runnerContext->getParallelOptions());
        }
        return $this->runTasksSequentially($tasks, $context);
    }

    protected function runTasksSequentially(TasksCollection $tasks, ContextInterface $context): TaskResultCollection
    {
        $taskResults = new TaskResultCollection();

        $this->eventDispatcher->dispatch(RunnerEvents::RUNNER_RUN, new RunnerEvent($tasks, $context, $taskResults));

        $this->runTasksInSequence($tasks, $context, $taskResults);

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
     * @param TasksCollection $tasks
     * @param ContextInterface $context
     * @param TaskResultCollection $taskResults
     * @return TaskResultCollection
     */
    protected function runTasksInSequence(
        TasksCollection $tasks,
        ContextInterface $context,
        TaskResultCollection $taskResults
    ) {
        /**
         * @var TaskInterface $task
         */
        foreach ($tasks as $task) {
            try {
                $taskResult = $this->runTask($task, $context);
            } catch (RuntimeException $e) {
                $taskResult = TaskResult::createFailed($task, $context, $e->getMessage());
            }

            // TODO
            // Note: changed from $taskResults->add($taskResult);
            // for better comparability in tests
            $taskResults[$task->getName()] = $taskResult;
            if (!$taskResult->isPassed() && $taskResult->isBlocking() && $this->grumPHP->stopOnFailure()) {
                break;
            }
        }
        return $taskResults;
    }

    /**
     * @param TaskInterface $task
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
            $this->eventDispatcher->dispatch(TaskEvents::TASK_SKIPPED, new TaskEvent($task, $context));

            return TaskResult::createSkipped($task, $context);
        } catch (RuntimeException $e) {
            $result = TaskResult::createFailed($task, $context, $e->getMessage());
        }

        return $this->revalidateTaskResult($task, $result, $context);
    }

    /**
     * @param TasksCollection $tasks
     * @param ContextInterface $context
     * @param ParallelOptions $options
     * @return TaskResultCollection
     */
    protected function runTasksParallely(
        TasksCollection $tasks,
        ContextInterface $context,
        ParallelOptions $options
    ): TaskResultCollection {
        $taskResults = new TaskResultCollection();
        $this->eventDispatcher->dispatch(RunnerEvents::RUNNER_RUN, new RunnerEvent($tasks, $context, $taskResults));

        $parallelTasks = $tasks->filter(function ($task) {
            return $task instanceof ParallelTaskInterface;
        });

        $taskResults = $this->runTasksInParallel($parallelTasks, $context, $options, $taskResults);

        $sequentialTasks = $tasks->filter(function ($task) {
            return !$task instanceof ParallelTaskInterface;
        });
        $taskResults     = $this->runTasksInSequence($sequentialTasks, $context, $taskResults);

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
     * @param TasksCollection $tasks
     * @param ContextInterface $context
     * @param ParallelOptions $options
     * @param TaskResultCollection $taskResults
     * @return TaskResultCollection
     */
    protected function runTasksInParallel(
        TasksCollection $tasks,
        ContextInterface $context,
        ParallelOptions $options,
        TaskResultCollection $taskResults
    ) {
        /**
         * @var ParallelTaskInterface[] $tasksToRun
         */
        $tasksToRun = [];
        foreach ($tasks as $task) {
            $tasksToRun[$task->getName()] = $task;
        }

        /**
         * @var Process[] $runningProcesses
         */
        $runningProcesses    = [];
        $maxProcesses        = $options->getMaxProcesses();
        $taskNamesToRun      = array_keys($tasksToRun);
        $executionShouldStop = false;
        do {
            $this->sleepIfNecessary($taskNamesToRun, $runningProcesses, $maxProcesses, $options->getSleep());

            $taskName = $this->getNextTaskName($taskNamesToRun, $runningProcesses, $maxProcesses);

            // start a new process for the given task
            if ($taskName !== null) {
                $task = $tasksToRun[$taskName];
                try {
                    $this->eventDispatcher->dispatch(TaskEvents::TASK_RUN, new TaskEvent($task, $context));
                    $process = $task->resolveProcess($context);
                    $process->start();
                    $runningProcesses[$taskName] = $process;
                } catch (PlatformException $e) {
                    $this->eventDispatcher->dispatch(TaskEvents::TASK_SKIPPED, new TaskEvent($task, $context));
                    $taskResults[$taskName] = TaskResult::createSkipped($task, $context);
                } catch (RuntimeException $e) {
                    $taskResults[$taskName] = TaskResult::createFailed($task, $context, $e->getMessage());
                }
            }

            // regularly check all running processes if they are finished
            foreach ($runningProcesses as $taskName => $process) {
                if (!$process->isRunning()) {
                    unset($runningProcesses[$taskName]);
                    $task = $tasksToRun[$taskName];

                    try {
                        $result = $task->getTaskResult($process, $context);
                        $result = $this->revalidateTaskResult($task, $result, $context);
                    } catch (\Throwable $t) {
                        $result = TaskResult::createFailed($task, $context, $t->getMessage());
                    }
                    $taskResults[$taskName] = $result;

                    if (!$result->isPassed() && $result->isBlocking() && $this->grumPHP->stopOnFailure()) {
                        $executionShouldStop = true;
                        break;
                    }
                }
            }

            if ($executionShouldStop) {
                // clear all remaining tasks
                $taskNamesToRun = [];
                // terminate all running processes
                // their results will be calculated accordingly in the next iteration
                foreach ($runningProcesses as $taskName => $process) {
                    try {
                        $process->stop(0);
                    } catch (\Throwable $t) {
                        // Sending SIGKILL is the best we can do
                    }
                }
            }

            $someProcessesAreStillRunning = count($runningProcesses) > 0;
            $notAllProcessesAreStartedYet = count($taskNamesToRun) > 0;
        } while ($someProcessesAreStillRunning || $notAllProcessesAreStartedYet);

        return $taskResults;
    }

    /**
     * Makes sure that the given TaskResult has the correct type
     * and takes care of dispatching the necessary events.
     *
     * @param TaskInterface $task
     * @param TaskResultInterface $result
     * @param ContextInterface $context
     * @return TaskResultInterface
     */
    protected function revalidateTaskResult(
        TaskInterface $task,
        TaskResultInterface $result,
        ContextInterface $context
    ): TaskResultInterface {
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
            $this->eventDispatcher->dispatch(TaskEvents::TASK_FAILED, new TaskFailedEvent($task, $context, $e));

            return $result;
        }

        $this->eventDispatcher->dispatch(TaskEvents::TASK_COMPLETE, new TaskEvent($task, $context));

        return $result;
    }

    /**
     * We should sleep when the processes are running in order to not
     * exhaust system resources. But we only wanna do this when
     * we can't start another processes:
     * either because none are left or
     * because we reached the threshold of allowed processes
     *
     * @param array $taskIdsToRun
     * @param array $runningProcesses
     * @param int $maxProcesses
     * @param int $secondsToSleep
     */
    private function sleepIfNecessary(
        array $taskIdsToRun,
        array $runningProcesses,
        int $maxProcesses,
        int $secondsToSleep
    ) {
        $noMoreProcessesAreLeft         = count($taskIdsToRun) === 0;
        $maxNumberOfProcessesAreRunning = count($runningProcesses) >= $maxProcesses;
        if ($noMoreProcessesAreLeft || $maxNumberOfProcessesAreRunning) {
            sleep($secondsToSleep);
        }
    }

    /**
     * @param array $taskIdsToRun
     * @param array $runningProcesses
     * @param int $max
     * @return string|null
     */
    private function getNextTaskName(array &$taskIdsToRun, array &$runningProcesses, int $max)
    {
        $moreProcessesAreLeft      = count($taskIdsToRun) > 0;
        $moreProcessesCanBeStarted = count($runningProcesses) < $max;
        if ($moreProcessesAreLeft && $moreProcessesCanBeStarted) {
            $taskName = array_shift($taskIdsToRun);
            return $taskName;
        }
        return null;
    }
}
