<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use GrumPHP\TestSuite\TestSuiteInterface;
use SplPriorityQueue;

/**
 * @extends ArrayCollection<int, TaskInterface>
 */
class TasksCollection extends ArrayCollection
{
    public function filterByContext(ContextInterface $context): self
    {
        return $this->filter(function (TaskInterface $task) use ($context) {
            return $task->canRunInContext($context);
        });
    }

    public function filterByTestSuite(?TestSuiteInterface $testSuite = null): self
    {
        if (null === $testSuite) {
            return $this;
        }

        return $this->filter(function (TaskInterface $task) use ($testSuite) {
            return \in_array($task->getConfig()->getName(), $testSuite->getTaskNames(), true);
        });
    }

    /**
     * @param string[] $tasks
     */
    public function filterByTaskNames(array $tasks): self
    {
        if (empty($tasks)) {
            return $this;
        }

        return $this->filter(function (TaskInterface $task) use ($tasks) {
            return \in_array($task->getConfig()->getName(), $tasks, true);
        });
    }

    /**
     * This method sorts the tasks by highest priority first.
     */
    public function sortByPriority(): self
    {
        $priorityQueue = new SplPriorityQueue();
        $stableSortIndex = PHP_INT_MAX;
        /** @var TaskInterface $task */
        foreach ($this->getIterator() as $task) {
            $metadata = $task->getConfig()->getMetadata();
            $priorityQueue->insert($task, [$metadata->priority(), $stableSortIndex--]);
        }

        return new self(array_values(iterator_to_array($priorityQueue)));
    }

    /**
     * @return array<int, TasksCollection>
     */
    public function groupByPriority(): array
    {
        return array_reduce(
            $this->toArray(),
            /**
             * @param array<int, TasksCollection> $grouped
             * @return array<int, TasksCollection>
             */
            static function (array $grouped, TaskInterface $task): array {
                $priority = $task->getConfig()->getMetadata()->priority();
                $groupTasks = $grouped[$priority] ?? new TasksCollection();
                $groupTasks->add($task);
                $grouped[$priority] = $groupTasks;

                return $grouped;
            },
            []
        );
    }
}
