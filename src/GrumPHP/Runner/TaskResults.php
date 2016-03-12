<?php

namespace GrumPHP\Runner;

class TaskResults implements \IteratorAggregate
{
    /**
     * @var TaskResult[]
     */
    private $taskResults = array();

    /**
     * @param TaskResult $taskResult
     */
    public function add(TaskResult $taskResult)
    {
        $this->taskResults[] = $taskResult;
    }

    /**
     * @return bool
     */
    public function isPassed()
    {
        foreach ($this->taskResults as $taskResult) {
            if (!$taskResult->isPassed()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->taskResults);
    }
}
