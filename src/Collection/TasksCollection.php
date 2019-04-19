<?php

declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use GrumPHP\TestSuite\TestSuiteInterface;
use SplPriorityQueue;

class TasksCollection extends ArrayCollection
{
    public function filterByContext(ContextInterface $context): self
    {
        return $this->filter(function (TaskInterface $task) use ($context) {
            return $task->canRunInContext($context);
        });
    }

    /**
     * @param TestSuiteInterface|null $testSuite
     */
    public function filterByTestSuite(TestSuiteInterface $testSuite = null): self
    {
        if (null === $testSuite) {
            return $this;
        }

        return $this->filter(function (TaskInterface $task) use ($testSuite) {
            return \in_array($task->getName(), $testSuite->getTaskNames(), true);
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
            return \in_array($task->getName(), $tasks, true);
        });
    }

    /**
     * This method sorts the tasks by highest priority first.
     */
    public function sortByPriority(GrumPHP $grumPHP): self
    {
        $priorityQueue = new SplPriorityQueue();
        $stableSortIndex = PHP_INT_MAX;
        foreach ($this->getIterator() as $task) {
            $metadata = $grumPHP->getTaskMetadata($task->getName());
            $priorityQueue->insert($task, [$metadata['priority'], $stableSortIndex--]);
        }

        return new self(array_values(iterator_to_array($priorityQueue)));
    }
}
