<?php declare(strict_types=1);

namespace GrumPHP\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use GrumPHP\Configuration\GrumPHP;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use GrumPHP\TestSuite\TestSuiteInterface;
use SplPriorityQueue;

class TasksCollection extends ArrayCollection
{
    /**
     * @return TasksCollection
     */
    public function filterByContext(ContextInterface $context): self
    {
        return $this->filter(function (TaskInterface $task) use ($context) {
            return $task->canRunInContext($context);
        });
    }

    /**
     * @return TasksCollection
     */
    public function filterByTestSuite(TestSuiteInterface $testSuite = null): self
    {
        if ($testSuite === null) {
            return new self($this->toArray());
        }

        return $this->filter(function (TaskInterface $task) use ($testSuite) {
            return in_array($task->getName(), $testSuite->getTaskNames(), true);
        });
    }

    /**
     * This method sorts the tasks by highest priority first.
     *
     *
     * @return TasksCollection
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
